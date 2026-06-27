import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/model_layer/search_products_model.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../../model_layer/wishlist_model.dart';

part 'search_state.dart';

class SearchCubit extends Cubit<SearchState> {
  SearchCubit() : super(SearchInitial());

  //////////////////////////////////[BASIC CONFIGURATION]////////////////////////////////////////////
  bool isLoading = false;
  setIsLoadingTrue() {
    isLoading = true;
    emit(SearchInitial());
  }
  setIsLoadingFalse() {
    isLoading = false;
    emit(SearchInitial());
  }
  TextEditingController controller = TextEditingController();
  String baseUrl = '${Constants.apiBaseUrl}products/search';
  Map<String, String> queryParams = {
    'search': '',
    'page': '1',
  };


  //////////////////////////////////[Suggestions CONFIGURATION]////////////////////////////////////////////
  String? searchWord;
  List<String> suggestedWords = [];
  String suggestedKey = 'suggested';
  setSearchWord(String word){
    searchWord = word;
    controller.text = searchWord??'';
    emit(SearchInitial());
  }
  createSearchSuggestions(String word) async {
    var res = suggestedWords.firstWhere((element) => (word == element), orElse: () => '-1',);
    if(res != '-1'){
      return null;
    }
    suggestedWords.add(word);
    SharedPreferences prefs = await SharedPreferences.getInstance();
    prefs.setStringList(suggestedKey, suggestedWords);
    emit(SearchInitial());
  }
  getSearchSuggestions() async {
    SharedPreferences prefs = await SharedPreferences.getInstance();
    suggestedWords = prefs.getStringList(suggestedKey)??[];
    emit(SearchInitial());
  }

  //////////////////////////////////[PAGINATION CONFIGURATION]////////////////////////////////////////////
  int currentPage = 1;
  int lastPage = 10;

  void nextPage() {
    if (currentPage < lastPage) {
      currentPage++;
      getSearchedProducts();
      emit(SearchInitial());
    }
  }

  void previousPage() {
    if (currentPage > 1) {
      currentPage--;
      getSearchedProducts();
      emit(SearchInitial());
    }
  }

  void setCurrentPage(int page) {
    if (page < 1 || page > lastPage) {
      return;
    }
    currentPage = page;
    getSearchedProducts();
    emit(SearchInitial());
  }

  void setLastPage(int page) {
    lastPage = page;
    emit(SearchInitial());
  }

  void resetPagination() {
    currentPage = 1;
    lastPage = 1;
    emit(SearchInitial());
  }

  bool isNotPaging() => currentPage == lastPage && lastPage == 1;

  //////////////////////////////////[SEARCH AND FILTERS CONFIGURATION]////////////////////////////////////////////

  double min = 0;
  double max = 0;
  String? selectedCategory;
  String? childCategoryId;
  String? selectedSortBy;
  SearchProductsModel? products;

  setMinMaxPrice(double minimum,double maximum){
    min = minimum;
    max = maximum;
    print(min);
    print(max);
  }

  setSelectedCategory(String selectedId){
    childCategoryId = null;
    selectedCategory = selectedId;
    emit(SearchInitial());
  }

  setSelectedSubCategory(String selectedId){
    childCategoryId = selectedId;
    emit(SearchInitial());
  }

  void resetFilters() {
    queryParams = {
      'search': '',
      'page': '1',
    };
    controller.clear();
    min = 0;
    max = 0;
    selectedCategory = null;
    selectedSortBy = null;
    childCategoryId = null;
    emit(SearchInitial());
  }

  void addSortBy(SortOptions option, String locale) {
    queryParams['sort_by'] = option.key!;
    selectedSortBy = locale == 'ar' ? option.nameArabic : option.nameGerman;
    emit(SearchInitial());
  }

  void prepareFilteredSearchUrl() {
    queryParams['search'] = controller.text;
    queryParams['page'] = currentPage.toString();

    if (selectedCategory != null) {
      queryParams['category_id'] = selectedCategory!;
    } else {
      queryParams.remove('category_id');
    }
    if (childCategoryId != null) {
      queryParams['child_category_id'] = childCategoryId!;
    }
    else {
      queryParams.remove('child_category_id');
    }
    if (min != 0) {
      queryParams['price_from'] = min.toString();
    }
    else {
      queryParams.remove('price_from');
    }
    if (max != 0) {
      queryParams['price_to'] = max.toString();
    }
    else {
      queryParams.remove('price_to');
    }

    emit(SearchInitial());
  }

  String buildUrl() {
    prepareFilteredSearchUrl();
    final uri = Uri.parse(baseUrl).replace(queryParameters: queryParams);
    return uri.toString();
  }

  Future<void> getSearchedProducts() async {
    String url = buildUrl();

    try {
      setIsLoadingTrue();
      print(url);

      final response = await http.get(
        Uri.parse(url),
        headers: {
          'Content-Type': 'application/json',
        },
      );

      setIsLoadingFalse();
      print(response.body);
      print(response.statusCode);

      final Map<String, dynamic> responseData = json.decode(response.body);
      products = SearchProductsModel.fromJson(responseData);

      if (response.statusCode == 200 || response.statusCode == 201) {
        if (products?.status == true) {
          lastPage = int.tryParse(products?.data?.products?.lastPage??'1') ?? 1;
          emit(SearchInitial());
          // Helpers.showColoredToast(
          //   color: Colors.greenAccent,
          //   message: 'Got Products Successfully!',
          // );
          return;
        }
      } else {
        String? errorMessage = responseData['errors'] != null
            ? Helpers.concatenateErrors(responseData['errors'])
            : null;
        Helpers.showColoredToast(
          color: Colors.red,
          message: errorMessage ?? 'Unexpected Error',
        );
      }
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(
        color: Colors.red,
        message: 'An error occurred: $e',
      );
      print(e.toString());
    }
  }

  //////////////////////////////////////[WISHLIST CONFIGURATION]////////////////////////////////////////////
  int selectedId = -1;
  setSelectedId(int id) {
    selectedId = id;
    emit(SearchInitial());
  }

  removeSelectedId() {
    selectedId = -1;
    emit(SearchInitial());
  }
  bool isWishlistLoading = false;
  
  // Set to track product IDs currently being processed to prevent race conditions
  final Set<int> _wishlistOperationsInProgress = {};

  WishlistModel? wishlist;

  setIsWishlistLoadingTrue() {
    isWishlistLoading = true;
    emit(SearchInitial());
  }

  setIsWishlistLoadingFalse() {
    isWishlistLoading = false;
    emit(SearchInitial());
  }

  Future add2Wishlist({String? token,required int product_id}) async {
    // Prevent duplicate operations for the same product
    if (_wishlistOperationsInProgress.contains(product_id)) {
      print('Wishlist add operation already in progress for product: $product_id');
      return 'Operation in progress';
    }
    _wishlistOperationsInProgress.add(product_id);
    
    String apiUrl = '${Constants.apiBaseUrl}wishlist';
    var body = {
      "product_id": product_id,
    };

    try {
      // Send POST request to the API
      setSelectedId(product_id);
      setIsWishlistLoadingTrue();
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode(body),
      );
      setIsWishlistLoadingFalse();
      removeSelectedId();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          Helpers.wishlistItems[product_id] = true;
          Helpers.wishlistProducts[product_id] = responseData['data']?['id'] ?? 0;
          _wishlistOperationsInProgress.remove(product_id);
          // Successful operation
          Helpers.showColoredToast(color: Colors.greenAccent, message: '${responseData['message']}');
          return null;
        }
      } else {
        _wishlistOperationsInProgress.remove(product_id);
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: "$errorMessage\n${responseData['message']}");
          return errorMessage;
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}: ${responseData['message'] ?? ''}');
      _wishlistOperationsInProgress.remove(product_id);
      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      _wishlistOperationsInProgress.remove(product_id);
      removeSelectedId();
      setIsWishlistLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  Future deleteFromWishlist({String? token,required int itemId,required int product_id}) async {
    // Prevent duplicate operations for the same product
    if (_wishlistOperationsInProgress.contains(product_id)) {
      print('Wishlist delete operation already in progress for product: $product_id');
      return 'Operation in progress';
    }
    _wishlistOperationsInProgress.add(product_id);
    
    String apiUrl = '${Constants.apiBaseUrl}wishlist/$itemId';

    try {
      // Send DELETE request to the API
      setSelectedId(itemId);
      setIsWishlistLoadingTrue();
      final response = await http.delete(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );
      removeSelectedId();

      setIsWishlistLoadingFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          Helpers.wishlistItems[product_id] = false;
          Helpers.wishlistProducts.remove(product_id);
          _wishlistOperationsInProgress.remove(product_id);
          // Successful operation
          Helpers.showColoredToast(color: Colors.greenAccent, message: '${responseData['message']}');
          return null;
        }
      } else {
        _wishlistOperationsInProgress.remove(product_id);
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: "$errorMessage\n${responseData['message']}");
          return errorMessage;
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}: ${responseData['message'] ?? ''}');
      _wishlistOperationsInProgress.remove(product_id);
      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      _wishlistOperationsInProgress.remove(product_id);
      removeSelectedId();
      setIsWishlistLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  Future getWishlist({String? token}) async {
    String apiUrl = '${Constants.apiBaseUrl}wishlist';

    try {
      // Send POST request to the API
      setIsWishlistLoadingTrue();
      final response = await http.get(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );
      setIsWishlistLoadingFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      wishlist = WishlistModel.fromJson(responseData);
      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          print("daaaaaaaaaaaaaaaaaaaaaaaaaaa:: ${wishlist?.data?.length}");
          for(WishlistItemData? item in wishlist?.data ?? []) {
            Helpers.wishlistItems[item?.productId??0] = true;
            Helpers.wishlistProducts[item?.productId ?? 0] = item?.id??0;
            emit(SearchInitial());
          }
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Wishlist Successfully!');
          return null;
        }
      }
      else {
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
      setIsWishlistLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }
}
