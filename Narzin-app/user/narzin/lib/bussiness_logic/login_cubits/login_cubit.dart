import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:narzin/core/constants.dart';
import 'package:narzin/model_layer/forget_password_model.dart';
import 'package:narzin/model_layer/login_model.dart';
import 'package:fluttertoast/fluttertoast.dart';
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


  LoginModel? loginModel;
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
    String? email = await _secureStorage.read(key: 'email');
    String? password = await _secureStorage.read(key: 'password');
    if(email != null && password != null){
      var res = await login(mail: email,pass: password);
      if(res == null){
        isRememberMeSucceeded = true;
        emit(LoginInitial());
        return null;
      }
      return 'There is an error';
    }


  }

  Future login({String? mail,String? pass}) async {
    String apiUrl =
        '${Constants.apiBaseUrl}login';

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
      loginModel = LoginModel.fromJson(responseData);

      // Check response status
      switch (response.statusCode) {
        case 200:
          if (loginModel?.status == true) {
            showColoredToast(color: Colors.greenAccent, message: 'Login successful.');
            if(rememberMe){
              await saveCreds(mail: mail?? email.text,pass: pass?? password.text);
            }
            return null;
          }
          break;

        case 403:
          if (loginModel?.data?.verificationRequired == true) {
            resendUrl = loginModel?.data?.resendVerificationUrl ?? '';
            String message = 'Account verification required.';
            showColoredToast(color: Colors.red, message: message);
            return '$message Resend verification email using this link: $resendUrl';
          }
          break;

        case 422:
          final errors = loginModel?.errors;
          if (errors != null) {
            String errorMessage = concatenateErrors(responseData['errors']);
            showColoredToast(color: Colors.red, message: errorMessage);
            return errorMessage;
          }
          break;

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
      print(response.body);

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
      showColoredToast(color: Colors.red, message: "$unexpectedError\n${forgetPasswordModel?.message??''}");
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

  resetEverything(){
    // Wipe any stored credentials on logout (SharedPreferences is cleared
    // separately by the caller; secure storage must be cleared here).
    _secureStorage.delete(key: 'email');
    _secureStorage.delete(key: 'password');
    email.clear();
    password.clear();
    confirmPassword.clear();
    rememberMe = false;
    isLoading = false;
    isVisible = false;
    isResend = false;
    loginModel = null;
    forgetPasswordModel = null;
    resendUrl = null;
    isRememberMeSucceeded = false;
    emit(LoginInitial());
  }

}
