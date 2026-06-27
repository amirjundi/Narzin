import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:meta/meta.dart';
import 'package:http/http.dart' as http;
import 'package:narzin/core/constants.dart';
import 'package:narzin/model_layer/forget_password_model.dart';
import 'package:narzin/model_layer/login_model.dart';
import 'package:fluttertoast/fluttertoast.dart';
import 'package:narzin/model_layer/vendor_login_model.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';


part 'login_state.dart';

class LoginCubit extends Cubit<LoginState> {
  LoginCubit() : super(LoginInitial());
  TextEditingController email = TextEditingController();
  TextEditingController password = TextEditingController();
  TextEditingController confirmPassword = TextEditingController();

  bool rememberMe = false;
  bool isLoading = false;
  setIsLoadingTrue(){
    isLoading = true;
    emit(LoginInitial());
  }
  setIsLoadingFalse(){
    isLoading = false;
    emit(LoginInitial());
  }

  bool isVisible = false;
  toggleIsVisible(){
    isVisible = !isVisible;
    emit(LoginInitial());
  }

  bool isResend = false;
  setIsResendTrue(){
    isResend = true;
    emit(LoginInitial());
  }
  setIsResendFalse(){
    isResend = false;
    emit(LoginInitial());
  }

  ForgetPasswordModel? forgetPasswordModel;
  String? resendUrl;

  bool isRememberMeSucceeded = false;

  toggleRememberMe(bool val){
    rememberMe = val;
    emit(LoginInitial());
  }


  // Credentials are sensitive and must not sit in plaintext SharedPreferences.
  // flutter_secure_storage keeps them in the iOS Keychain / Android Keystore.
  final FlutterSecureStorage _secureStorage = const FlutterSecureStorage();

  saveCreds({required String mail,required String pass}) async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.setBool('rememberMe', true);
    await _secureStorage.write(key: 'email', value: mail);
    await _secureStorage.write(key: 'password', value: pass);
  }
  handleRememberMe() async {
    isRememberMeSucceeded = false;
    emit(LoginInitial());
    String? _email = await _secureStorage.read(key: 'email');
    String? _password = await _secureStorage.read(key: 'password');
    if(_email != null && _password != null){
      var res = await vendorLogin(mail: _email,pass: _password);
      if(res == null){
        isRememberMeSucceeded = true;
        emit(LoginInitial());
        return null;
      }
      return 'There is an error';
    }


  }

  VendorLoginModel? vendorData;
  Future vendorLogin({String? mail,String? pass}) async {
    String apiUrl =
        '${Constants.apiBaseUrl}login?vendor';

    try {
      // Start loading
      setIsLoadingTrue();

      // Send POST request
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'email':mail?? email.text,
          'password':pass?? password.text,
        }),
      );


      // Stop loading
      setIsLoadingFalse();

      // Parse the response
      final responseData = json.decode(response.body) as Map<String, dynamic>;
      vendorData = VendorLoginModel.fromJson(responseData);
      if (vendorData?.status == true) {
        if(rememberMe){
          await saveCreds(mail: mail?? email.text,pass: pass?? password.text);
        }
        showColoredToast(color: Colors.greenAccent, message: 'Login successful.');
        return null;
      }else{
        if (responseData['errors'] != null) {
          String errorMessage = concatenateErrors(responseData['errors']);
          showColoredToast(color: Colors.red, message: errorMessage);
          return errorMessage;
        }
        switch (response.statusCode) {
          case 401:
            showColoredToast(color: Colors.red, message: 'Unauthorized: Incorrect credentials or access denied.');
            return 'Unauthorized: Incorrect credentials or access denied.';

          case 500:
            showColoredToast(color: Colors.red, message: 'Server Error: Please try again later.');
            return 'Server Error: Please try again later.';

          default:
            String unexpectedError = 'Unexpected Error: Status Code ${response.statusCode}.';
            showColoredToast(color: Colors.red, message: unexpectedError);
            return unexpectedError;
        }

      }

      // Check response status

    } catch (e) {
      // Handle exceptions
      setIsLoadingFalse();
      String errorMessage = 'An error occurred: $e';
      showColoredToast(color: Colors.red, message: errorMessage);
      return errorMessage;
    }
  }


  String concatenateErrors(Map<String, dynamic> errors) {
    List<String> errorMessages = [];

    errors.forEach((key, value) {
      if (value is List) {
        for (var message in value) {
          errorMessages.add("$key: $message");
        }
      }
    });

    return errorMessages.join("\n"); // Join messages with a newline or any delimiter
  }


  Future forgetPassword() async {
    String apiUrl =
        '${Constants.apiBaseUrl}forgot-password';

    try {
      // Start loading
      setIsLoadingTrue();

      // Send POST request
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {'Content-Type': 'application/json'},
        body: json.encode({
          'email': email.text,
        }),
      );
      // print(response.body);

      // Stop loading
      setIsLoadingFalse();

      // Parse response
      final responseData = json.decode(response.body) as Map<String, dynamic>;
      forgetPasswordModel = ForgetPasswordModel.fromJson(responseData);

      // Check status and response codes
      if (response.statusCode >= 200 &&
          response.statusCode <= 208 &&
          forgetPasswordModel?.status == true) {
        showColoredToast(
          color: Colors.greenAccent,
          message: forgetPasswordModel?.message ?? 'Success',
        );
        return null;
      }

      // Handle errors
      final errors = forgetPasswordModel?.errors;
      if (errors != null && errors.email != null) {
        String errorMessage = 'Email: ${errors.email!.join(', ')}';
        showColoredToast(color: Colors.red, message: errorMessage);
        return errorMessage;
      }

      // Handle unexpected errors
      String unexpectedError = 'Unexpected Error: Status Code ${response.statusCode}.';
      showColoredToast(color: Colors.red, message: unexpectedError+"\n"+(forgetPasswordModel?.message??''));
      return unexpectedError;

    } catch (e) {
      // Handle exceptions
      setIsLoadingFalse();
      String errorMessage = 'An error occurred: $e';
      showColoredToast(color: Colors.red, message: errorMessage);
      return errorMessage;
    }
  }

  void showColoredToast({String? message, Color? color, Color? textColor}) {
      Fluttertoast.showToast(
        msg: message ?? 'No Toast',
        toastLength: Toast.LENGTH_SHORT,
        backgroundColor: color,
        textColor: textColor ?? Colors.white,
      );

  }

}
