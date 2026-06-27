import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart';
import 'package:latlong2/latlong.dart';
import 'package:geolocator/geolocator.dart' as geo;
import 'package:narzin/core/constants.dart';
import 'package:narzin/model_layer/coupons_model.dart';
import 'package:narzin/model_layer/my_orders_model.dart';
import 'package:narzin/model_layer/delivery_zones_model.dart';
import 'package:narzin/model_layer/single_order_model.dart';
import 'package:path_provider/path_provider.dart';
import 'package:http/http.dart' as http;

import '../../core/helpers.dart';
import '../../model_layer/order_model.dart';

part 'order_state.dart';

class OrderCubit extends Cubit<OrderState> {
  OrderCubit() : super(OrderInitial());

  String? latitude;
  String? longitude;
  geo.Position? position;
  MapController ordersMapController = MapController();

  Future isLocationEnabled() async {
    geo.LocationPermission permission;

    await geo.Geolocator.requestPermission();

    permission = (await geo.Geolocator.checkPermission());
    if (permission == LocationPermission.denied) {
      emit(OrderInitial());
      permission = (await geo.Geolocator.requestPermission());
      if (permission == LocationPermission.denied) {
        emit(OrderInitial());
        // Fluttertoast.showToast(msg: S.of(context).locationDenied, backgroundColor: Colors.red);
        return null;
      }
    }
    emit(OrderInitial());
    if (permission == LocationPermission.deniedForever) {
      emit(OrderInitial());
      // Fluttertoast.showToast(msg: S.of(context).locationDeniedForever, backgroundColor: Colors.red);
      return null;
    }
  }
  String? path;
  Future<String> getPath() async {
    final cacheDirectory = await getTemporaryDirectory();
    path = cacheDirectory.path;
    return cacheDirectory.path;
  }
  bool isOrdersMapInitialized = false;

  bool isLoading = false;

  setIsLoadingTrue() {
    isLoading = true;
    emit(OrderInitial());
  }

  setIsLoadingFalse() {
    isLoading = false;
    emit(OrderInitial());
  }

  Future getCoordinates() async {
    position = null;
    setIsLoadingTrue();
    await isLocationEnabled();

    emit(OrderInitial());
    position = await geo.Geolocator.getCurrentPosition();
    emit(OrderInitial());
    print(position?.longitude);
    print(position?.latitude);
    setIsLoadingFalse();
    if (kDebugMode) {
      Helpers.showColoredToast(message: 'Got Location', color: Colors.green);
    }
    if (isOrdersMapInitialized) {
      initializeOrdersMapController();
    }
    return null;
  }

  captureNewPosition(LatLng latlng) {
    position = geo.Position(
      latitude: latlng.latitude,
      // Set your desired latitude
      longitude: latlng.longitude,
      // Set your desired longitude
      timestamp: DateTime.now(),
      // Set the timestamp
      altitude: 0.0,
      // Altitude (optional)
      accuracy: 0.0,
      // Accuracy (optional)
      heading: 0.0,
      // Heading (optional)
      speed: 0.0,
      // Speed (optional)
      speedAccuracy: 0.0,
      altitudeAccuracy: 0.0,
      headingAccuracy: 0.0, // Speed accuracy (optional)
    );
    ordersMapController.move(latlng, 19.10683711643711);
    initializeOrdersMapController();
    emit(OrderInitial());
  }

  initializeOrdersMapController() {
    isOrdersMapInitialized = true;
    ordersMapController.move(LatLng(position?.latitude ?? 0, position?.longitude ?? 0), 19.10683711643711);
  }

  int? selectedDeliveryMethodId;

  setSelectedDeliveryMethodId(int? methodId) {
    selectedDeliveryMethodId = methodId;
    emit(OrderInitial());
  }

  TextEditingController couponController = TextEditingController();

  PlaceOrderModel? placeOrderModel;

  Future placeOrder({String? token,required int address_id,bool wallet=false}) async {
    String apiUrl = '${Constants.apiBaseUrl}place-order';
    var body = {
      "address_id": address_id,
      "delivery_method_id": selectedDeliveryMethodId,
      "coupon" : couponController.text,
      "wallet": wallet ? 1 : 0  // Send as integer for API compatibility
    };
    print(json.encode(body));

    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode(body),
      );
      setIsLoadingFalse();
      print(response.body);
      print(response.statusCode);
      
      // Check if response is HTML (server error) before trying to parse JSON
      if (response.body.trimLeft().startsWith('<!DOCTYPE') || 
          response.body.trimLeft().startsWith('<html')) {
        Helpers.showColoredToast(color: Colors.red, message: 'Server error. Please try again later.');
        return 'Server returned an HTML error page.';
      }
      
      // Parse the JSON response
      final Map<String, dynamic> responseData = json.decode(response.body);
      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          placeOrderModel = PlaceOrderModel.fromJson(responseData);
          // Successful login
          print(responseData);
          Helpers.showColoredToast(color: Colors.greenAccent, message: '${responseData['message']}');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: "$errorMessage\n${responseData['message']}");
          return errorMessage;
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}: ${responseData['message'] ?? ''}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  CouponsModel? couponsModel;

  String? couponMessage;
  bool isCouponApplied = false;
  resetEveryThing(){
    couponMessage = null;
    isCouponApplied = false;
    couponController.clear();
    couponsModel = null;
    emit(OrderInitial());
  }
  setCouponMessage(String? message,bool isApplied){
    couponMessage = message;
    isCouponApplied = isApplied;
    emit(OrderInitial());
  }

  Future applyCoupon({String? token, String? coupon}) async {
    String apiUrl = '${Constants.apiBaseUrl}get-coupon';
    var body = {
      "code": coupon??couponController.text,
    };

    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode(body),
      );
      setIsLoadingFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      // Handle response
      couponsModel = CouponsModel.fromJson(responseData);
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          // Successful login
          Helpers.showColoredToast(color: Colors.greenAccent, message: 'Coupon got successfully!');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: "$errorMessage\n${responseData['message']}");
          return errorMessage;
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}: ${responseData['message'] ?? ''}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  MyOrdersModel? myOrdersModel;
  Future getMyOrder({required String token}) async {
    String apiUrl = '${Constants.apiBaseUrl}orders';
    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );
      setIsLoadingFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      myOrdersModel = MyOrdersModel.fromJson(responseData);
      for(MyOrder order in myOrdersModel?.data?.data??[]){
        print("##################");
        print(order.orderStatus);
        print("#####################");
      }

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (myOrdersModel?.status == true) {
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got my order successfully\n${myOrdersModel?.message}');
          return null;
        }
      }
      Helpers.showColoredToast(color: Colors.red, message: '${myOrdersModel?.message}');
      if (responseData['errors'] != null) {
        String errorMessage = Helpers.concatenateErrors(responseData['errors']);
        Helpers.showColoredToast(color: Colors.red, message: errorMessage.trim());
        return errorMessage.trim();
      }

    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  DeliveryZonesModel? deliveryZonesModel;
  Future getDeliveryZones({required String token}) async {
    String apiUrl = '${Constants.apiBaseUrl}get-delivery-zones';
    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );
      setIsLoadingFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      deliveryZonesModel = DeliveryZonesModel.fromJson(responseData);
      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (deliveryZonesModel?.status == true) {
          return null;
        }
      }
      Helpers.showColoredToast(color: Colors.red, message: '${deliveryZonesModel?.message}');
      if (responseData['errors'] != null) {
        String errorMessage = Helpers.concatenateErrors(responseData['errors']);
        Helpers.showColoredToast(color: Colors.red, message: errorMessage.trim());
        return errorMessage.trim();
      }

    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  SingleOrderModel? singleOrder;
  Future getSingleOrder({required String token,String? id}) async {
    String apiUrl = '${Constants.apiBaseUrl}orders/$id';
    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );
      setIsLoadingFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      singleOrder = SingleOrderModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (singleOrder?.status == true) {
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got my order successfully}');
          return null;
        }
      }
      Helpers.showColoredToast(color: Colors.red, message: '${singleOrder?.message}');
      if (responseData['errors'] != null) {
        String errorMessage = Helpers.concatenateErrors(responseData['errors']);
        Helpers.showColoredToast(color: Colors.red, message: errorMessage.trim());
        return errorMessage.trim();
      }

    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }


  Future changeOrderStatus({required String token,String? id}) async {
    String apiUrl = '${Constants.apiBaseUrl}orders/$id/change-status';
    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );
      setIsLoadingFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (singleOrder?.status == true) {
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: "Order status changed successfully${responseData['message']}");
          return null;
        }
      }
      Helpers.showColoredToast(color: Colors.red, message: '${responseData["message"]}');
      if (responseData['errors'] != null) {
        String errorMessage = Helpers.concatenateErrors(responseData['errors']);
        Helpers.showColoredToast(color: Colors.red, message: errorMessage.trim());
        return errorMessage.trim();
      }

    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }



}
