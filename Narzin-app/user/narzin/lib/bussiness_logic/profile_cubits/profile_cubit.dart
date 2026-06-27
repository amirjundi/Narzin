import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:geolocator/geolocator.dart' as geo;
import 'package:geolocator/geolocator.dart';
import 'package:http/http.dart' as http;
import 'package:latlong2/latlong.dart';
import 'package:logger/logger.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/model_layer/addresses_model.dart';
import 'package:narzin/model_layer/countries_model.dart';
import 'package:narzin/model_layer/delivery_zones_model.dart';
import 'package:narzin/model_layer/profile_model.dart';
import 'package:path_provider/path_provider.dart';

part 'profile_state.dart';

class ProfileCubit extends Cubit<ProfileState> {
  ProfileCubit() : super(ProfileInitial());

  String? latitude;
  String? longitude;
  geo.Position? position;
  MapController mapController = MapController();

  Future isLocationEnabled() async {
    geo.LocationPermission permission;

    await geo.Geolocator.requestPermission();

    permission = (await geo.Geolocator.checkPermission());
    if (permission == LocationPermission.denied) {
      emit(ProfileInitial());
      permission = (await geo.Geolocator.requestPermission());
      if (permission == LocationPermission.denied) {
        emit(ProfileInitial());
        // Fluttertoast.showToast(msg: S.of(context).locationDenied, backgroundColor: Colors.red);
        return null;
      }
    }
    emit(ProfileInitial());
    if (permission == LocationPermission.deniedForever) {
      emit(ProfileInitial());
      // Fluttertoast.showToast(msg: S.of(context).locationDeniedForever, backgroundColor: Colors.red);
      return null;
    }
  }

  bool isAddressMapInitialized = false;

  Future getCoordinates() async {
    position = null;
    setIsLoadingTrue();
    await isLocationEnabled();

    emit(ProfileInitial());
    position = await geo.Geolocator.getCurrentPosition();
    emit(ProfileInitial());
    print(position?.longitude);
    print(position?.latitude);
    setIsLoadingFalse();
    if (kDebugMode) {
      Helpers.showColoredToast(message: 'Got Location', color: Colors.green);
    }
    if (isAddressMapInitialized) {
      initializeMapController();
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
    mapController.move(latlng, 19.10683711643711);
    initializeMapController();
    emit(ProfileInitial());
  }

  initializeMapController() {
    isAddressMapInitialized = true;
    mapController.move(LatLng(position?.latitude ?? 0, position?.longitude ?? 0), 19.10683711643711);
  }



  String? path;

  Future<String> getPath() async {
    final cacheDirectory = await getTemporaryDirectory();
    path = cacheDirectory.path;
    return cacheDirectory.path;
  }

  TextEditingController email = TextEditingController();
  TextEditingController name = TextEditingController();
  TextEditingController password = TextEditingController();
  TextEditingController confirmPassword = TextEditingController();
  TextEditingController currentPassword = TextEditingController();
  bool isNameEditable = false;
  bool notificationEnabled = false;

  toggleNotificationEnabled() {
    notificationEnabled = !notificationEnabled;
    emit(ProfileInitial());
  }

  bool isVisible = false;

  setIsVisible() {
    isVisible = !isVisible;
    emit(ProfileInitial());
  }

  setIsNameEditable() {
    isNameEditable = !isNameEditable;
    emit(ProfileInitial());
  }

  bool isEmailEditable = false;

  setIsEmailEditable() {
    isEmailEditable = !isEmailEditable;
    emit(ProfileInitial());
  }

  setControllers() {
    email.text = profile?.data?.user?.email ?? '';
    name.text = profile?.data?.user?.name ?? '';
    password.text = '';
    confirmPassword.text = '';
    currentPassword.text = '';
    isEmailEditable = false;
    isNameEditable = false;
    emit(ProfileInitial());
  }

  localizeAddress(String address){
    emit(ProfileInitial());
    return Helpers.formatLangFullAddress(location: address,);
  }

  AddressesModel? addressesModel;
  Future getAddresses({String? token}) async {
    String apiUrl = '${Constants.apiBaseUrl}address';
    try {
      // Start loading
      setIsLoadingTrue();

      // Send POST request
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      // Stop loading
      setIsLoadingFalse();

      // Parse the response
      final responseData = json.decode(response.body) as Map<String, dynamic>;
      Logger l = Logger();
      // print(responseData);
      l.t(responseData);

      addressesModel = AddressesModel.fromJson(responseData);

      if (response.statusCode == 200 || response.statusCode == 201) {
        if (addressesModel?.status == true) {

          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Countries Retrieval successful.');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
        Helpers.showColoredToast(color: Colors.red, message: "$errorMessage\n ${responseData['message']}" ?? 'Unauthorized: Incorrect credentials or access denied.');
        return errorMessage ?? 'Unauthorized: access denied.';
      }

      String unexpectedError = addressesModel?.message ?? 'Unexpected Error: Status Code ${response.statusCode}.';
      Helpers.showColoredToast(color: Colors.red, message: unexpectedError);
      return unexpectedError;
    } catch (e) {
      // Handle exceptions
      setIsLoadingFalse();
      String errorMessage = 'An error occurred: $e';
      print(errorMessage);
      Helpers.showColoredToast(color: Colors.red, message: errorMessage);
      return errorMessage;
    }
  }
  Future setIsAddressesDefault({String? token,String? id}) async {
    String apiUrl = '${Constants.apiBaseUrl}address/$id/set-default';
    try {
      // Start loading
      setIsLoadingTrue();

      // Send POST request
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      // Stop loading
      setIsLoadingFalse();

      // Parse the response
      final responseData = json.decode(response.body) as Map<String, dynamic>;
      Logger l = Logger();
      // print(responseData);
      l.t(responseData);


      if (response.statusCode == 200 || response.statusCode == 201) {
        if (addressesModel?.status == true) {
          Helpers.showColoredToast(color: Colors.greenAccent, message:responseData['message']);
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
        Helpers.showColoredToast(color: Colors.red, message: "$errorMessage\n ${responseData['message']}" ?? 'Unauthorized: Incorrect credentials or access denied.');
        return errorMessage ?? 'Unauthorized: access denied.';
      }

      String unexpectedError = addressesModel?.message ?? 'Unexpected Error: Status Code ${response.statusCode}.';
      Helpers.showColoredToast(color: Colors.red, message: unexpectedError);
      return unexpectedError;
    } catch (e) {
      // Handle exceptions
      setIsLoadingFalse();
      String errorMessage = 'An error occurred: $e';
      print(errorMessage);
      Helpers.showColoredToast(color: Colors.red, message: errorMessage);
      return errorMessage;
    }
  }

  Future deleteAddress({String? token,String? address_id}) async {
    String apiUrl = '${Constants.apiBaseUrl}address/$address_id';
    try {
      // Start loading
      setIsLoadingTrue();

      // Send POST request
      final response = await http.delete(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      // Stop loading
      setIsLoadingFalse();

      // Parse the response
      final responseData = json.decode(response.body) as Map<String, dynamic>;
      Logger l = Logger();
      // print(responseData);
      l.t(responseData);

      addressesModel = AddressesModel.fromJson(responseData);

      if (response.statusCode == 200 || response.statusCode == 201) {
        if (addressesModel?.status == true) {

          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Countries Retrieval successful.');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
        Helpers.showColoredToast(color: Colors.red, message: "$errorMessage\n ${responseData['message']}" ?? 'Unauthorized: Incorrect credentials or access denied.');
        return errorMessage ?? 'Unauthorized: access denied.';
      }

      String unexpectedError = addressesModel?.message ?? 'Unexpected Error: Status Code ${response.statusCode}.';
      Helpers.showColoredToast(color: Colors.red, message: unexpectedError);
      return unexpectedError;
    } catch (e) {
      // Handle exceptions
      setIsLoadingFalse();
      String errorMessage = 'An error occurred: $e';
      print(errorMessage);
      Helpers.showColoredToast(color: Colors.red, message: errorMessage);
      return errorMessage;
    }
  }
  String? selectedAddress;
  String? showAddress;

  setSelectedGroup(String group,String address) {
    showAddress = address;
    selectedAddress = group;
    emit(ProfileInitial());
  }

  bool isLoading = false;

  setIsLoadingTrue() {
    isLoading = true;
    emit(ProfileInitial());
  }

  setIsLoadingFalse() {
    isLoading = false;
    emit(ProfileInitial());
  }

  ProfileModel? profile;

  Future getProfile({String? token}) async {
    String apiUrl = '${Constants.apiBaseUrl}profile';

    try {
      // Start loading
      setIsLoadingTrue();

      // Send POST request
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      // Stop loading
      setIsLoadingFalse();

      // Parse the response
      final responseData = json.decode(response.body) as Map<String, dynamic>;
      Logger l = Logger();
      print('######################################################');
      // print(responseData);
      l.t(responseData);
      print('######################################################');
      profile = ProfileModel.fromJson(responseData);
      print('######################################################');

      // Check response status
      switch (response.statusCode) {
        case 200:
          if (profile?.status == true) {
            // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Profile Retrieval successful.');
            return null;
          }
          break;

        case 401:
          String? errorMessage;
          if (responseData['errors'] != null) {
            errorMessage = Helpers.concatenateErrors(responseData['errors']);
            Helpers.showColoredToast(color: Colors.red, message: errorMessage);
          }
          Helpers.showColoredToast(color: Colors.red, message: errorMessage ?? 'Unauthorized: Incorrect credentials or access denied.');
          return errorMessage ?? 'Unauthorized: Incorrect credentials or access denied.';

        default:
          String unexpectedError = profile?.message ?? 'Unexpected Error: Status Code ${response.statusCode}.';
          Helpers.showColoredToast(color: Colors.red, message: unexpectedError);
          return unexpectedError;
      }
    } catch (e) {
      // Handle exceptions
      setIsLoadingFalse();
      String errorMessage = 'An error occurred: $e';
      print(errorMessage);
      Helpers.showColoredToast(color: Colors.red, message: errorMessage);
      return errorMessage;
    }
  }



  TextEditingController phone = TextEditingController();
  TextEditingController postalCode = TextEditingController();
  TextEditingController city = TextEditingController();
  TextEditingController apartmentNo = TextEditingController();
  TextEditingController buildingNo = TextEditingController();
  TextEditingController fullAddress = TextEditingController();
  bool isDefault = false;
  TextEditingController titleController = TextEditingController();
  DeliveryZonesModel? deliveryZones;
  DeliveryZone? selectedDeliveryZone;

  toggleIsDefault() {
    isDefault = !isDefault;
    emit(ProfileInitial());
  }

  setSelectedDeliveryZone(DeliveryZone? zone) {
    selectedDeliveryZone = zone;
    emit(ProfileInitial());
  }

  Cities? selectedCity;

  setSelectedCity(Cities? city) {
    selectedCity = city;
    emit(ProfileInitial());
  }

  resetEveryThing(){
    phone.clear();
    postalCode.clear();
    city.clear();
    apartmentNo.clear();
    buildingNo.clear();
    fullAddress.clear();
    titleController.clear();
    isDefault = false;
    selectedDeliveryZone = null;
    selectedCity = null;
  }

  Future getDeliveryZones({String? token}) async {
    String apiUrl = '${Constants.apiBaseUrl}get-delivery-zones';
    deliveryZones = null;
    selectedDeliveryZone = null;
    selectedCity = null;
    try {
      // Start loading
      setIsLoadingTrue();

      // Send POST request
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      // Stop loading
      setIsLoadingFalse();

      // Parse the response
      final responseData = json.decode(response.body) as Map<String, dynamic>;
      Logger l = Logger();
      // print(responseData);
      l.t(responseData);

      deliveryZones = DeliveryZonesModel.fromJson(responseData);

      if (response.statusCode == 200 || response.statusCode == 201) {
        if (deliveryZones?.status == true) {
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Zones Retrieval successful.');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
        Helpers.showColoredToast(color: Colors.red, message: "$errorMessage\n ${responseData['message']}" ?? 'Unauthorized: Incorrect credentials or access denied.');
        return errorMessage ?? 'Unauthorized: access denied.';
      }

      String unexpectedError = deliveryZones?.message ?? 'Unexpected Error: Status Code ${response.statusCode}.';
      Helpers.showColoredToast(color: Colors.red, message: unexpectedError);
      return unexpectedError;
    } catch (e) {
      // Handle exceptions
      setIsLoadingFalse();
      String errorMessage = 'An error occurred: $e';
      print(errorMessage);
      Helpers.showColoredToast(color: Colors.red, message: errorMessage);
      return errorMessage;
    }
  }

  Future addAddress({String? token}) async {
    String apiUrl = '${Constants.apiBaseUrl}address';
    // String concatenatedAddress = Helpers.formatFullAddress(
    //   fullAddress: fullAddress.text,
    //   city: selectedCity?.name ?? '',
    //   country: selectedCountry?.name ?? '',
    //   street: street.text,
    //   apartmentNo: apartmentNo.text,
    //   buildingNo: buildingNo.text,
    // );
    var body = {
      "address": fullAddress.text,
      "title":titleController.text,
      "city": city.text,
      "phone_number" : phone.text,
      "is_default" :isDefault?1:0,
      "delivery_zone_id": selectedDeliveryZone?.id
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
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          // Successful login
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

  Future updateProfile({String? token, required int choice}) async {
    String apiUrl = '${Constants.apiBaseUrl}profile/update';
    var body = {};

    if (choice == 0) {
      body = {"name": name.text, "current_password": currentPassword.text, "email": email.text, "password": password.text, "password_confirmation": confirmPassword.text};
    } else {
      body = {
        "name": name.text,
        "email": email.text,
      };
    }

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
      profile = ProfileModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (profile?.status == true) {
          // Successful login
          Helpers.showColoredToast(color: Colors.greenAccent, message: '${profile?.message}');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}: ${profile?.message ?? ''}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }
}
