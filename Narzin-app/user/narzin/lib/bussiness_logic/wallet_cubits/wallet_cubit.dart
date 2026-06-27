import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:narzin/core/constants.dart';
import 'package:http/http.dart' as http;
import 'package:narzin/core/helpers.dart';
import 'package:narzin/model_layer/wallet_model.dart';
import 'package:narzin/model_layer/wallet_transactions_model.dart';

part 'wallet_state.dart';

class WalletCubit extends Cubit<WalletState> {
  WalletCubit() : super(WalletInitial());

  bool isLoading = false;
  bool isSelected = false;

  setIsLoadingTrue() {
    isLoading = true;
    emit(WalletInitial());
  }

  setIsLoadingFalse() {
    isLoading = false;
    emit(WalletInitial());
  }

  toggleIsSelected() {
    isSelected = !isSelected;
    emit(WalletInitial());
  }



  WalletModel? wallet;
  Future getWallet({required String token}) async {
    String apiUrl = '${Constants.apiBaseUrl}wallet';
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
      wallet = WalletModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (wallet?.status == true) {
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Wallet successfully\n${wallet?.message}');
          return null;
        }
      }
      Helpers.showColoredToast(color: Colors.red, message: '${wallet?.message}');
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

  WalletTransactionModel? walletTransactions;
  Future getWalletTransactions({required String token}) async {
    String apiUrl = '${Constants.apiBaseUrl}get-wallet-transactions';
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
      if (kDebugMode) {
        print(response.body);
      }
      if (kDebugMode) {
        print(response.statusCode);
      }
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      walletTransactions = WalletTransactionModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (walletTransactions?.status == true) {
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Wallet successfully\n${wallet?.message}');
          return null;
        }
      }
      Helpers.showColoredToast(color: Colors.red, message: '${walletTransactions?.message}');
      if (responseData['errors'] != null) {
        String errorMessage = Helpers.concatenateErrors(responseData['errors']);
        Helpers.showColoredToast(color: Colors.red, message: errorMessage.trim());
        return errorMessage.trim();
      }

    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      if (kDebugMode) {
        print(e.toString());
      }
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }
}
