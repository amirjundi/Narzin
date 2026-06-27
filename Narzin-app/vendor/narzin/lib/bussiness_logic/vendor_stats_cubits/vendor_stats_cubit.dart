import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:meta/meta.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/model_layer/order_model.dart';
import 'package:narzin/model_layer/statistics_model.dart';
import 'package:http/http.dart' as http;

import '../../core/helpers.dart';
import '../../model_layer/single_produt_model.dart';

part 'vendor_stats_state.dart';

class VendorStatsCubit extends Cubit<VendorStatsState> {
  VendorStatsCubit() : super(VendorStatsInitial());

  String? selectedDropDownVal;
  int selectedFilterIndex = 0;


  setSelectedValue(String? selected){
    selectedDropDownVal = selected;
    emit(VendorStatsInitial());
  }
  setSelectedFilterIndex(int index){
    selectedFilterIndex = index;
    emit(VendorStatsInitial());
  }

  bool isLoading = false;
  setIsLoadingTrue() {
    isLoading = true;
    emit(VendorStatsInitial());
  }

  setIsLoadingFalse() {
    isLoading = false;
    emit(VendorStatsInitial());
  }

  StatisticsModel? statistics;
  Future getStatistics({required String token}) async {
    statistics = null;
    String apiUrl = '${Constants.apiBaseUrl}vendor/orders/statistics';

    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token'
        },
      );
      setIsLoadingFalse();
      // print(response.body);
      // print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      statistics = StatisticsModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (statistics?.status == true) {
          emit(VendorStatsInitial());
          Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got My Statistics Successfully!');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}/${statistics?.message}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      // print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }




  int selectedProductIndex = 0;

  setSelectedProductIndex(int index){
    selectedProductIndex = index;
    emit(VendorStatsInitial());
  }

  OrdersData? selectedOrder;

  setSelectedOrder(OrdersData? order){
    selectedProductIndex = 0;
    selectedOrder = order;
    emit(VendorStatsInitial());
  }

  OrdersResponse? orders;
  Future getOrders({required String token}) async {
    String apiUrl = '${Constants.apiBaseUrl}vendor/orders';

    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token'
        },
      );
      setIsLoadingFalse();
      // print(response.body);
      // print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      orders = OrdersResponse.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (orders?.status == true) {
          emit(VendorStatsInitial());
          Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got My Orders Successfully!');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}/${orders?.message}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      // print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }



  Future changeOrderStatus({required String token,required String id,required String status}) async {
    String apiUrl = '${Constants.apiBaseUrl}vendor/orders/$id/status';

    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.put(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token'
        },
        body: json.encode({
          'status' : status
        }),
      );
      setIsLoadingFalse();
      // print(response.body);
      // print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      orders = OrdersResponse.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (orders?.status == true) {
          emit(VendorStatsInitial());
          Helpers.showColoredToast(color: Colors.greenAccent, message: 'Changed Order Status Successfully!');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}/${orders?.message}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      // print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }


  bool isSingleProductLoading = false;
  setIsSingleProductLoadingTrue() {
    isSingleProductLoading = true;
    emit(VendorStatsInitial());
  }

  setIsSingleProductLoadingFalse() {
    isSingleProductLoading = false;
    emit(VendorStatsInitial());
  }

  SingleProductModel? singleProduct;
  Future getSingleProduct({required int id,required int index}) async {
    singleProduct = null;
    String apiUrl = '${Constants.apiBaseUrl}products/$id';
    setSelectedProductIndex(index);
    try {
      // Send POST request to the API
      setIsSingleProductLoadingTrue();
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
        },
      );
      setIsSingleProductLoadingFalse();
      // print(response.body);
      // print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      singleProduct = SingleProductModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (singleProduct?.status == true) {
          emit(VendorStatsInitial());
          // Successful login
          Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Products Successfully!');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}/${singleProduct?.message}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsSingleProductLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      // print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }



}
