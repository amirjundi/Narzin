import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:narzin/core/helpers.dart';

import '../../core/constants.dart';
import '../../model_layer/banners_model.dart';
import 'package:http/http.dart' as http;

part 'banners_state.dart';

class BannersCubit extends Cubit<BannersState> {
  BannersCubit() : super(BannersInitial());

  bool isLoading = false;
  setIsLoadingFalse(){
    isLoading = false;
    emit(BannersInitial());
  }
  setIsLoadingTrue(){
    isLoading = true;
    emit(BannersInitial());
  }


  BannersModel? bannersModel;
  Future getBanners({String? token}) async {
    bannersModel = null;
    String apiUrl =
        '${Constants.apiBaseUrl}banners/mobile';

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
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      bannersModel = BannersModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (bannersModel?.status == true) {

          // Helpers.showColoredToast(
          //     color: Colors.greenAccent, message: 'Got My Cart Successfully!');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
      }

      Helpers.showColoredToast(
          color: Colors.red,
          message:
          'Unexpected Error: Status Code ${response.statusCode}/${bannersModel?.message}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(
          color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

}
