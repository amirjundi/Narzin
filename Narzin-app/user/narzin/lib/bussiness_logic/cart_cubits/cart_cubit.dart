import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/model_layer/add_to_cart_body_model.dart';
import 'package:narzin/model_layer/add_to_cart_model.dart';
import 'package:narzin/model_layer/my_cart_model.dart';
import 'package:narzin/model_layer/single_product_model.dart';
part 'cart_state.dart';

class CartCubit extends Cubit<CartState> {
  CartCubit() : super(CartInitial());
  bool isLoading = false;
  bool isLoadingQuantity = false;
  bool isLoadingDeleteItem = false;

  setIsLoadingTrue() {
    isLoading = true;
    emit(CartInitial());
  }

  setIsLoadingFalse() {
    isLoading = false;
    emit(CartInitial());
  }

  setIsLoadingQuantityTrue() {
    isLoadingQuantity = true;
    emit(CartInitial());
  }

  setIsLoadingQuantityFalse() {
    isLoadingQuantity = false;
    emit(CartInitial());
  }

  setIsLoadingDeleteTrue() {
    isLoadingDeleteItem = true;
    emit(CartInitial());
  }

  setIsLoadingDeleteFalse() {
    isLoadingDeleteItem = false;
    emit(CartInitial());
  }



  int quantity = 1;
  AddToCartBodyModel? cartBody;

  SingleProductVariants? selectedVariant;
  selectVariant(
    SingleProductVariants? variant,
  ) {
    selectedVariant = variant;
    emit(CartInitial());
  }

  resetCartBody() {
    cartBody = null;
    selectedVariant = null;
    discountedItems.clear();
    quantity = 1;
    emit(CartInitial());
  }

  formulateCartBody(String variantId, int id) {
    if (variantId.isNotEmpty) {
      cartBody = AddToCartBodyModel(
        productId: id.toString(),
        productVariantId: variantId,
        quantity: quantity.toString(),
      );
    }
    emit(CartInitial());
  }

  addQuantity(int quant) {
    quantity = quant;
    emit(CartInitial());
  }

  AddToCartResponseModel? addToCartResponse;

  MyCartModel? myCart;
  Map<String, int> cartQuantities = {};

  double taxes = 0;
  double totalPrice = 0;
  double totalWeight = 0;
  double totalAfterDiscount = 0;
  double discount = 0;
  Map<String, double> discountedItems = {};

  applyDiscount(double discountAmount, String discountType,bool isApplied,String? vendorId){
    discountedItems.clear();
    if(isApplied){
      discount = 0;
      if(discountType == 'percentage'){
        bool isVendorIdExists = false;
        if (vendorId != null) {
          for(var item in myCart!.data!){
            if(item.product?.vendorId == vendorId){
              isVendorIdExists = true;
              break;
            }
          }
        }
        for(var item in myCart!.data!){
          if(isVendorIdExists){
            if(item.product?.vendorId == vendorId){
              double actualDiscount = (double.tryParse(item.price??'')??0)* (discountAmount/ 100);
              discount += actualDiscount;
              discountedItems[item.id.toString()] = (double.tryParse(item.price??'')??0) - actualDiscount;
            }else{
              discountedItems[item.id.toString()] = double.tryParse(item.price??'')??0;
            }
          }else{
            double actualDiscount = (double.tryParse(item.price??'')??0)* (discountAmount/ 100);
            discount += actualDiscount;
            discountedItems[item.id.toString()] = (double.tryParse(item.price??'')??0) - actualDiscount;
          }
        }
        double total = 0;
        for (var entry in (discountedItems.entries)) {
          total += entry.value;
        }
        totalAfterDiscount = total;
      }else{
        discountedItems.clear();
        discount = discountAmount;
        totalAfterDiscount = totalPrice - discountAmount;
      }
    }else{

      discountedItems.clear();
      totalAfterDiscount = totalPrice;
    }

    emit(CartInitial());
  }

  getCartQuants() {
    cartQuantities.clear();
    if (myCart == null || (myCart?.data?.isEmpty ?? true)) {
      // Helpers.showColoredToast(message: 'Cart is Empty.',color: Colors.red);
      return;
    }
    for (var cartItem in (myCart?.data)!) {
      if(cartItem.outOfStock == false){
        cartQuantities.addAll({"${cartItem.id}": int.tryParse(cartItem.quantity.toString()) ?? 0});
      }
    }
    emit(CartInitial());
  }

  getCartTotal() {
    totalPrice = 0;
    totalWeight = 0;
    if (myCart == null || (myCart?.data?.isEmpty ?? true)) {
      // Helpers.showColoredToast(message: 'Cart is Empty.',color: Colors.red);
      return;
    }
    for (var cartItem in (myCart?.data)!) {
      if(cartItem.outOfStock == false){
        int quant = cartQuantities[(cartItem.id ?? 0).toString()] ?? 0;
        totalPrice += (quant.toDouble() *
            (double.tryParse(cartItem.productVariant?.price.toString() ?? '0') ??
                0));
        totalWeight += (quant.toDouble() *
            (double.tryParse(cartItem.product?.weight ?? '0') ?? 0));
      }

    }
    totalAfterDiscount = totalPrice;
    emit(CartInitial());
  }

  getCartTaxes(double shippingCost) {
    getCartTotal();
    taxes = shippingCost;
    totalAfterDiscount+=taxes;
    emit(CartInitial());
  }

  getTotals(double balance,double shippingCost) {
    getCartTotal();
    getCartTaxes(shippingCost);
    if(balance > totalAfterDiscount){
      totalAfterDiscount = 0;
    }else{
      totalAfterDiscount-=balance;
    }
    emit(CartInitial());
  }

  Future getMyCart({required String token}) async {
    myCart = null;
    String apiUrl =
        '${Constants.apiBaseUrl}cart';

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
      myCart = MyCartModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (myCart?.status == true) {
          getCartQuants();
          getCartTotal();
          emit(CartSuccess());
          Helpers.showColoredToast(
              color: Colors.greenAccent, message: 'Got My Cart Successfully!');
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
              'Unexpected Error: Status Code ${response.statusCode}/${myCart?.message}');

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

  Future clearMyCart({required String token}) async {
    String apiUrl =
        '${Constants.apiBaseUrl}clear/cart';
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

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          getCartQuants();
          emit(CartSuccess());
          Helpers.showColoredToast(
              color: Colors.greenAccent, message: responseData['message']);
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
              'Unexpected Error: Status Code ${response.statusCode}/${responseData['message']}');

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

  Future addToCart({required String token}) async {
    String apiUrl =
        '${Constants.apiBaseUrl}cart';
    addToCartResponse = null;
    if (cartBody == null) {
      Helpers.showColoredToast(
          message: 'Please Select variant and it\'s quantity first!!',
          color: Colors.red);
      return;
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
        body: json.encode(cartBody?.toJson()),
      );
      setIsLoadingFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      addToCartResponse = AddToCartResponseModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (addToCartResponse?.status == true) {
          // Successful login
          Helpers.showColoredToast(
              color: Colors.greenAccent,
              message: '${addToCartResponse?.message}');
          return null;
        }
      }
      Helpers.showColoredToast(
          color: Colors.red, message: '${addToCartResponse?.message}');
      if (responseData['errors'] != null) {
        String errorMessage = Helpers.concatenateErrors(responseData['errors']);
        Helpers.showColoredToast(
            color: Colors.red, message: errorMessage.trim());
        return errorMessage.trim();
      }
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(
          color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  setQuantity(int quant, String itemId) {
    cartQuantities[itemId] = quant;
    emit(CartInitial());
  }

  Future updateCartItemQuantity(
      {required String token, required int? itemID}) async {
    String apiUrl =
        '${Constants.apiBaseUrl}cart/$itemID';
    try {
      // Send POST request to the API
      setIsLoadingQuantityTrue();
      final response = await http.put(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode({"quantity": cartQuantities[itemID.toString()]}),
      );
      setIsLoadingQuantityFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          // Successful login
          Helpers.showColoredToast(
              color: Colors.greenAccent, message: '${responseData['message']}');
          return null;
        }
      }
      Helpers.showColoredToast(
          color: Colors.red, message: '${responseData['message']}');
      if (responseData['errors'] != null) {
        String errorMessage = Helpers.concatenateErrors(responseData['errors']);
        Helpers.showColoredToast(
            color: Colors.red, message: errorMessage.trim());
        return errorMessage.trim();
      }
    } catch (e) {
      setIsLoadingQuantityFalse();
      Helpers.showColoredToast(
          color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  Future deleteCartItem({required String token, required int? itemID}) async {
    String apiUrl =
        '${Constants.apiBaseUrl}cart/$itemID';
    try {
      // Send POST request to the API
      setIsLoadingDeleteTrue();
      final response = await http.delete(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );
      setIsLoadingDeleteFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          // Successful login
          Helpers.showColoredToast(
              color: Colors.greenAccent, message: '${responseData['message']}');
          return null;
        }
      }
      Helpers.showColoredToast(
          color: Colors.red, message: '${responseData['message']}');
      if (responseData['errors'] != null) {
        String errorMessage = Helpers.concatenateErrors(responseData['errors']);
        Helpers.showColoredToast(
            color: Colors.red, message: errorMessage.trim());
        return errorMessage.trim();
      }
    } catch (e) {
      setIsLoadingDeleteFalse();
      Helpers.showColoredToast(
          color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }
}
