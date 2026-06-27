import 'dart:convert';
import 'dart:io';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/model_layer/profile_model.dart';

part 'profile_state.dart';

class ProfileCubit extends Cubit<ProfileState> {
  ProfileCubit() : super(ProfileInitial());

  TextEditingController email = TextEditingController();
  TextEditingController name = TextEditingController();
  TextEditingController password = TextEditingController();
  TextEditingController confirmPassword = TextEditingController();
  TextEditingController currentPassword = TextEditingController();
  bool isNameEditable = false;
  bool notificationEnabled = false;
  toggleNotificationEnabled(){
    notificationEnabled = !notificationEnabled;
    emit(ProfileInitial());
  }
  bool isVisible = false;
  setIsVisible(){
    isVisible = !isVisible;
    emit(ProfileInitial());
  }
  setIsNameEditable(){
    isNameEditable = !isNameEditable;
    emit(ProfileInitial());
  }
  bool isEmailEditable = false;
  setIsEmailEditable(){
    isEmailEditable = !isEmailEditable;
    emit(ProfileInitial());
  }
  setControllers(){
    email.text = profile?.data?.user?.email??'';
    name.text = profile?.data?.user?.name??'';
    password.text = '';
    confirmPassword.text = '';
    currentPassword.text = '';
    isEmailEditable = false;
    isNameEditable = false;
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
      profile = ProfileModel.fromJson(responseData);

      // Check response status
      switch (response.statusCode) {
        case 200:
          if (profile?.status == true) {
            Helpers.showColoredToast(color: Colors.greenAccent, message: 'Profile Retrieval successful.');
            return null;
          }
          break;

        case 401:
          String? errorMessage;
          if (responseData['errors'] != null) {
            errorMessage = Helpers.concatenateErrors(responseData['errors']);
            Helpers.showColoredToast(color: Colors.red, message: errorMessage);
          }
          Helpers.showColoredToast(color: Colors.red, message: errorMessage?? 'Unauthorized: Incorrect credentials or access denied.');
          return errorMessage?? 'Unauthorized: Incorrect credentials or access denied.';

        default:
          String unexpectedError = profile?.message??'Unexpected Error: Status Code ${response.statusCode}.';
          Helpers.showColoredToast(color: Colors.red, message: unexpectedError);
          return unexpectedError;
      }
    } catch (e) {
      // Handle exceptions
      setIsLoadingFalse();
      String errorMessage = 'An error occurred: $e';
      Helpers.showColoredToast(color: Colors.red, message: errorMessage);
      return errorMessage;
    }
  }

  Future updateProfile({String? token,required int choice}) async {
    String apiUrl = '${Constants.apiBaseUrl}profile/update';
    var body = {};

    if(choice == 0){
      body = {
        "name":name.text,
        "current_password":currentPassword.text,
        "email":email.text,
        "password":password.text,
        "password_confirmation":confirmPassword.text
      };
    }
    else{
      body = {
        "name":name.text,
        "email":email.text,
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
      // print(response.body);
      // print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      profile = ProfileModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (profile?.status == true) {
          // Successful login
          Helpers.showColoredToast(color: Colors.greenAccent,message: '${profile?.message}');
          return null;
        }
      }else{
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
      }

        Helpers.showColoredToast(color: Colors.red,message: 'Unexpected Error: Status Code ${response.statusCode}: ${profile?.message??''}');

        return 'Unexpected Error: Status Code ${response.statusCode}.';

    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red,message: 'An error occurred: $e');
      // print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }


}
