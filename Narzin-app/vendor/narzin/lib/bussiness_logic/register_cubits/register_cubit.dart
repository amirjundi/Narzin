import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:meta/meta.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/model_layer/ResendVerificationModel.dart';
import 'package:narzin/model_layer/register_model.dart';

part 'register_state.dart';

class RegisterCubit extends Cubit<RegisterState> {
  RegisterCubit() : super(RegisterInitial());
  TextEditingController email = TextEditingController();
  TextEditingController name = TextEditingController();
  TextEditingController password = TextEditingController();
  TextEditingController confirmPassword = TextEditingController();

  bool isLoading = false;

  bool isVisible = false;
  bool isResend = false;
  setIsResendTrue(){
    isResend = true;
    emit(RegisterInitial());
  }
  setIsResendFalse(){
    isResend = false;
    emit(RegisterInitial());
  }

  setIsLoadingTrue(){
    isLoading = true;
    emit(RegisterInitial());
  }

  setIsLoadingFalse(){
    isLoading = false;
    emit(RegisterInitial());
  }

  toggleIsVisible(){
    isVisible = !isVisible;
    emit(RegisterInitial());
  }
  RegisterModel? registerModel;
  ResendVerificationModel? verificationModel;

  Future register() async {
    String apiUrl = '${Constants.apiBaseUrl}register';
    registerModel = null;
    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
        },
        body: json.encode({
          'email': email.text,
          'password': password.text,
          "name" : name.text ,
          "password_confirmation" : confirmPassword.text,
          "user_type_id":"2",
        }),
      );
      setIsLoadingFalse();
      // print(response.body);
      // print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      registerModel = RegisterModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {

        if (registerModel?.status == true) {

          // Successful login
          showColoredToast(color: Colors.greenAccent,message: '${registerModel?.message}');
          return null;
        }
      }
      else if (response.statusCode == 403) {
        // Handle account verification required case
        showColoredToast(color: Colors.red,message: 'Account verification required. ${response.statusCode}');
        return 'Account verification required.';
      }
      else if(registerModel?.errors != null || response.statusCode == 422){
        final errors = registerModel?.errors;
        String errorMessage = '';
        if (errors?.email != null) {
          errorMessage += 'Email: ${errors?.email!.join(', ')}. ';
        }
        if (errors?.password != null) {
          errorMessage += 'Password: ${errors?.password!.join(', ')}. ';
        }
        if (errors?.userTypeId != null) {
          errorMessage += 'User Type Id: ${errors?.userTypeId!.join(', ')}. ';
        }
        if (errors?.name != null) {
          errorMessage += 'Name: ${errors?.name!.join(', ')}. ';
        }
        showColoredToast(color: Colors.red,message: errorMessage.trim());
        return errorMessage.trim();
      }
      else if (response.statusCode == 401) {
        showColoredToast(color: Colors.red,message: 'Unauthorized: Incorrect credentials or access denied.');

        return 'Unauthorized: Incorrect credentials or access denied.';
      } else if (response.statusCode == 500) {
        showColoredToast(color: Colors.red,message: 'Server Error: Please try again later.');

        return 'Server Error: Please try again later.';
      } else {
        showColoredToast(color: Colors.red,message: 'Unexpected Error: Status Code ${response.statusCode}.');

        return 'Unexpected Error: Status Code ${response.statusCode}.';
      }
    } catch (e) {
      setIsLoadingFalse();
      showColoredToast(color: Colors.red,message: 'An error occurred: $e');
      // print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }
  Future resendVerification() async {
    int userId = 0;
    if(registerModel!=null){
      userId = registerModel?.data?.user?.id??0;
    }

    String apiUrl = '${Constants.apiBaseUrl}email/resend-verification/${userId}';

    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
        },
      );
      setIsLoadingFalse();
      // print(response.body);
      // print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      verificationModel = ResendVerificationModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {

        if (verificationModel?.status == true) {

          // Successful login
          showColoredToast(color: Colors.greenAccent,message: '${verificationModel?.message}');
          return null;
        }
      }
      else if (response.statusCode == 403) {
        // Handle account verification required case
        showColoredToast(color: Colors.red,message: 'Account verification required. ${response.statusCode}');
        return 'Account verification required.';
      }
      else if(verificationModel?.error != null){
        final errors = verificationModel?.error;
        showColoredToast(color: Colors.red,message: errors.toString().trim());
        return errors;
      }
      else if (response.statusCode == 401) {
        showColoredToast(color: Colors.red,message: 'Unauthorized: Incorrect credentials or access denied.');

        return 'Unauthorized: Incorrect credentials or access denied.';
      } else if (response.statusCode == 500) {
        showColoredToast(color: Colors.red,message: 'Failed to process verification email request.');

        return 'Failed to process verification email request.';
      } else {
        showColoredToast(color: Colors.red,message: 'Unexpected Error: Status Code ${response.statusCode}.');

        return 'Unexpected Error: Status Code ${response.statusCode}.';
      }
    } catch (e) {
      setIsLoadingFalse();
      showColoredToast(color: Colors.red,message: 'An error occurred: $e');
      // print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  void showColoredToast({String? message, Color? color, Color? textColor}) {
    Fluttertoast.showToast(
      msg: message ?? 'No Toast',
      toastLength: Toast.LENGTH_LONG,
      backgroundColor: color,
      textColor: textColor ?? Colors.white,
    );

  }
}
