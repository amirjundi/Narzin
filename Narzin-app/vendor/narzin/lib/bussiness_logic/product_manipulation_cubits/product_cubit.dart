import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:meta/meta.dart';
import 'package:http/http.dart' as http;
import 'package:narzin/core/helpers.dart';
import 'package:narzin/model_layer/vendor_products_model.dart';

import '../../core/constants.dart';
import '../../model_layer/categories_model.dart';
import '../../model_layer/single_produt_model.dart';

part 'product_state.dart';

class ProductCubit extends Cubit<ProductState> {
  ProductCubit() : super(ProductInitial());

  bool isLoading = false;
  setIsLoadingTrue() {
    isLoading = true;
    emit(ProductInitial());
  }

  setIsLoadingFalse() {
    isLoading = false;
    emit(ProductInitial());
  }

  List<bool> isSingleProductLoading = [];
  setIsSingleProductLoadingTrue(int index) {
    isSingleProductLoading[index] = true;
    emit(ProductInitial());
  }

  setIsSingleProductLoadingFalse(int index) {
    isSingleProductLoading[index] = false;
    emit(ProductInitial());
  }

  VendorProductsModel? vendorProducts;
  VendorProductsModel? filteredVendorProducts;
  int selectedFilterIndex = -1;

  CategoryData? filteredCategory;
  setSelectedFilterIndex(int index){
    selectedFilterIndex = index;
    filteredCategory = categories?.data?[index];
    setFilteredVendorProducts();
    emit(ProductInitial());
  }


  setFilteredVendorProducts(){
    filteredVendorProducts?.data = vendorProducts?.data?.where((element) => (element.category?.parentId??element.category?.id??0).toString() == (filteredCategory?.parentId??filteredCategory?.id??'0'),).toList();
    emit(ProductInitial());
  }

  Future getVendorProducts({required vendor_id}) async {
    vendorProducts = null;
    String apiUrl = '${Constants.apiBaseUrl}vendors/$vendor_id/products';

    try {
      // Send POST request to the API
      selectedFilterIndex = -1;
      setIsLoadingTrue();
      final response = await http.get(
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
      vendorProducts = VendorProductsModel.fromJson(responseData);
      filteredVendorProducts = VendorProductsModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (vendorProducts?.status == true) {
          // Successful login
          isSingleProductLoading = List<bool>.generate(vendorProducts?.data?.length??0,(index) => false,growable: true);
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

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      // print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }


  CategoriesModel? categories;
  Future getCategories() async {
    String apiUrl = '${Constants.apiBaseUrl}categories';

    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.get(
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
      categories = CategoriesModel.fromJson(responseData);
      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (categories?.status == true) {
          // Successful login
          Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Categories Successfully!');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: errorMessage);
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      // print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }





  SingleProductModel? singleProduct;
  Future getSingleProduct({required int id,required int index}) async {
    singleProduct = null;
    String apiUrl = '${Constants.apiBaseUrl}products/$id';

    try {
      // Send POST request to the API
      setIsSingleProductLoadingTrue(index);
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
        },
      );
      setIsSingleProductLoadingFalse(index);
      // print(response.body);
      // print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      print(111);
      singleProduct = SingleProductModel.fromJson(responseData);
      print(222);
      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (singleProduct?.status == true) {
          emit(ProductInitial());
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
      setIsSingleProductLoadingFalse(index);
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      // print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

}
