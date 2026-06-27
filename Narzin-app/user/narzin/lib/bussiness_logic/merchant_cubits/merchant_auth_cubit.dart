import 'dart:convert';
import 'dart:io';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/model_layer/merchant_register_model.dart';
import 'package:http/http.dart' as http;
part 'merchant_auth_state.dart';

class MerchantAuthCubit extends Cubit<MerchantAuthState> {
  MerchantAuthCubit() : super(MerchantAuthInitial());
  TextEditingController address = TextEditingController();
  TextEditingController name = TextEditingController();
  TextEditingController category = TextEditingController();
  TextEditingController arabicName = TextEditingController();
  TextEditingController englishName = TextEditingController();
  TextEditingController description = TextEditingController();
  TextEditingController phone = TextEditingController();

  bool isLoading = false;

  setIsLoadingTrue(){
    isLoading = true;
    emit(MerchantAuthInitial());
  }

  setIsLoadingFalse(){
    isLoading = false;
    emit(MerchantAuthInitial());
  }


  MerchantRegisterModel? merchantRegisterModel;
  final ImagePicker _imagePicker = ImagePicker();
  File? storeLogo;
  File? storeId;

  Future pickImageFromGallery({required int choise}) async {
    if(choise == 0){
      storeLogo = null;
    }else{
      storeId = null;
    }


    try {
      final XFile? pickedFile =
      await _imagePicker.pickImage(source: ImageSource.gallery);

      if (pickedFile != null) {
        if(choise == 0){
          storeLogo = File(pickedFile.path);
        }else{
          storeId = File(pickedFile.path);
        }

        emit(MerchantAuthInitial());
        Helpers.showColoredToast(message: "Successfully Selected.",color: Colors.greenAccent);
      } else {
        emit(MerchantAuthInitial());
        Helpers.showColoredToast(message: "No image selected.",color: Colors.red);
      }
    } catch (e) {
      emit(MerchantAuthInitial());
      Helpers.showColoredToast(message: "Failed to pick image: $e",color: Colors.red);
    }
  }


  // Assuming these functions, models, and helpers are defined elsewhere in your codebase:
// - setIsLoadingTrue(), setIsLoadingFalse()
// - Helpers.showColoredToast({required Color color, required String message})
// - Helpers.concatenateErrors(dynamic errors)
// - merchantRegisterModel and MerchantRegisterModel.fromJson(Map<String, dynamic> json)

  Future<String?> registerMerchant({required String token}) async {
    String apiUrl = '${Constants.apiBaseUrl}vendors';

    // Start loading state
    setIsLoadingTrue();

    try {
      var request = http.MultipartRequest('POST', Uri.parse(apiUrl));

      // Headers
      request.headers.addAll({
        'Authorization': 'Bearer $token',
      });

      // Fields
      request.fields.addAll({
        'store_name_in_arabic': arabicName.text,
        'store_name_in_german': englishName.text,
        'address': address.text,
        'phone': "${int.tryParse(phone.text)??0}",
        'store_type': category.text,
        'latitude': '0.0',
        'longitude': '0.0'
      });

      // Files - Update paths to actual file paths on your device or server
      request.files.add(await http.MultipartFile.fromPath(
        'store_logo',
        storeLogo?.path??'', // Example path
      ));
      request.files.add(await http.MultipartFile.fromPath(
        'store_id',
        storeId?.path??'', // Example path
      ));

      print('Sending Multipart Request: ${request.fields}');

      // Send the request
      http.StreamedResponse streamedResponse = await request.send();

      // Stop loading state
      setIsLoadingFalse();

      // Convert streamed response to string
      String responseBody = await streamedResponse.stream.bytesToString();
      print('Response: $responseBody');
      print('Status Code: ${streamedResponse.statusCode}');

      // Parse the response
      Map<String, dynamic>? responseData;
      try {
        responseData = jsonDecode(responseBody) as Map<String, dynamic>;
      } catch (e) {
        // JSON parsing error - treat as unexpected error
        Helpers.showColoredToast(color: Colors.red, message: 'Invalid JSON response');
        return 'Invalid JSON response';
      }

      // Build MerchantRegisterModel if JSON is valid
      merchantRegisterModel = MerchantRegisterModel.fromJson(responseData);

      if (streamedResponse.statusCode == 200 || streamedResponse.statusCode == 201) {
        // Success responses
        if (merchantRegisterModel?.status == true) {
          // Helpers.showColoredToast(
          //     color: Colors.greenAccent,
          //     message: '${merchantRegisterModel?.message}'
          // );
          return null; // Indicate success
        }
      } else {
        // Error responses
        String errorMessage = responseData['errors'] != null
            ? Helpers.concatenateErrors(responseData['errors'])
            : 'Unexpected Error: ${streamedResponse.statusCode}';

        if(merchantRegisterModel?.status == false){
          Helpers.showColoredToast(
              color: Colors.red,
              message: '${merchantRegisterModel?.message?? 'Registration failed'} :$errorMessage'
          );
          return merchantRegisterModel?.message ?? 'Registration failed';
        }

        // Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        return errorMessage;
      }
    } catch (e) {
      // Catch any other errors (network issues, file not found, etc.)
      setIsLoadingFalse();
      final String errorMsg = 'An error occurred: $e';
      Helpers.showColoredToast(color: Colors.red, message: errorMsg);
      print(errorMsg);
      return errorMsg;
    }
    return null;
  }
}
