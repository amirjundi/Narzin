import 'dart:convert';

import 'package:bloc/bloc.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:http/http.dart' as http;
import 'package:narzin/core/constants.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/model_layer/product_reviews_model.dart';
import 'package:narzin/model_layer/products_model.dart';
import 'package:narzin/model_layer/single_product_model.dart';
import 'package:narzin/model_layer/vendor_data_model.dart';
import 'package:narzin/model_layer/vendor_product_model.dart';
import 'package:narzin/model_layer/wishlist_model.dart';

import '../../core/helpers.dart';
import '../../model_layer/categories_model.dart';

part 'product_state.dart';

class ProductsCubit extends Cubit<ProductsState> {
  ProductsCubit() : super(ProductsInitial());

  int selectedFilterIndex = 0;

  setSelectedFilterIndex(int index) {
    selectedFilterIndex = index;
    emit(ProductsInitial());
  }

  bool isLoading = false;
  bool isLoading2 = false;

  setIsLoadingTrue() {
    isLoading = true;
    isLoading2 = true;
    emit(ProductsInitial());
  }

  setIsLoadingFalse() {
    isLoading = false;
    isLoading2 = false;
    emit(ProductsInitial());
  }

  String stock = '';

  ProductReviewsModel? productReviews;

  Future getProductReviews({required String token, required String? productId}) async {
    String apiUrl = '${Constants.apiBaseUrl}products/get/reviews';
    productReviews = null;
    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode({"product_id": productId}),
      );
      setIsLoadingFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      productReviews = ProductReviewsModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (productReviews?.status == true) {
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Reviews successfully\n${productReviews?.message}');
          return null;
        }
      }
      Helpers.showColoredToast(color: Colors.red, message: '${productReviews?.message}');
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

  TextEditingController comment = TextEditingController();
  double rating = 0;

  resetReviewForm() {
    comment = TextEditingController();
    rating = 0;
  }

  Future addProductReview({required String token, required String? productId}) async {
    String apiUrl = '${Constants.apiBaseUrl}reviews';
    try {
      // Send POST request to the API
      setIsLoadingTrue();
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: json.encode({"product_id": productId, "review": comment.text, "rating": "${rating.ceil()}"}),
      );
      setIsLoadingFalse();
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: '\n${responseData['message']}');
          return null;
        }
      }
      Helpers.showColoredToast(color: Colors.red, message: '${responseData['message']}');
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

  int selectedIndex = 0;

  setSelectedIndex(int index) {
    selectedIndex = index;
    emit(ProductsInitial());
  }

  bool isOutOfStock = false;

  setIsOutOfStock(variantId) {
    var variants = singleProduct?.data?.variants;
    var selectedVariant = variants?.firstWhere((element) => element.id.toString() == variantId);
    var value = selectedVariant?.stock == "0";
    isOutOfStock = value;
    emit(ProductsInitial());
  }

  createStock(BuildContext context) {
    stock = '';
    if (singleProduct != null) {
      var variants = singleProduct?.data?.variants;
      if (variants != null) {
        stock += '(';
        for (var variant in variants) {
          print(variant.stock);

          stock += '${variant.stock}, ';
        }
        stock = stock.substring(0, stock.length - 2);
        stock += ') ${S.of(context).available_stock}';
      }
    }
    emit(ProductsInitial());
  }

  int selectedScreen = 0;

  void changeSelectedIndex(int index) {
    selectedScreen = index;
    emit(ProductsInitial());
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
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      categories = CategoriesModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (categories?.status == true) {
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Categories Successfully!');
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
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  ProductsModel? products;

  Future getAllProducts() async {
    String apiUrl = '${Constants.apiBaseUrl}products';

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
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      products = ProductsModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (products?.status == true) {
          // Successful login
          emit(ProductsSuccess());
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Products Successfully!');
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
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  VendorDataModel? vendor;

  Future getVendorDetails({required vendor_id}) async {
    vendor = null;
    String apiUrl = '${Constants.apiBaseUrl}vendors/$vendor_id';

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
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      vendor = VendorDataModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (vendor?.status == true) {
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Products Successfully!');
          return null;
        }
      } else {
        String? errorMessage;
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
          Helpers.showColoredToast(color: Colors.red, message: '${vendor?.message}\n$errorMessage');
        }
      }

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}');

      return 'Unexpected Error: Status Code ${response.statusCode}.';
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  VendorProductsModel? vendorProducts;

  Future getVendorProducts({required vendor_id}) async {
    vendorProducts = null;
    String apiUrl = '${Constants.apiBaseUrl}vendors/$vendor_id/products';

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
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      vendorProducts = VendorProductsModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (vendorProducts?.status == true) {
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Products Successfully!');
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
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  SingleProductModel? singleProduct;

  Future getSingleProduct({required int id}) async {
    singleProduct = null;
    String apiUrl = '${Constants.apiBaseUrl}products/$id';

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
      print(response.body);
      print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      singleProduct = SingleProductModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (singleProduct?.status == true) {
          emit(SingleProductSuccess());
          // Successful login
          emit(ProductsSuccess());
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Products Successfully!');
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
      setIsLoadingFalse();
      Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: $e');
      print(e.toString());
      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }

  // ====================== VARIANT SELECTION ======================

  // اللون -> قائمة Variants
  Map<String, List<SingleProductVariants>> colorGroups = {};
  Map<String, String?> colorPatterns = {};
  Map<String, List<String>> otherAttributes = {};
  Map<String, int> selectedAttributes = {};

  String selectedColorValue = '';
  int selectedColorIndex = -1;
  String selectedVariantId = '';
  String? imageToShow;


// تعيين الصورة حسب اللون
  void setImageToShow() {
    if (selectedColorIndex == -1) return;

    final selectedColor = colorGroups.entries.elementAt(selectedColorIndex).key;
    print('selectedColor: $selectedColor');

    // هات أول variant مرتبط باللون المختار
    final variantsForColor = colorGroups[selectedColor];
    if (variantsForColor == null || variantsForColor.isEmpty) return;

    final variantId = variantsForColor.first.id;

    // دور على صورة مرتبطة باللون
    var filteredImage = singleProduct?.data?.images?.firstWhere(
          (element) => ((element.color?.contains('#')??false)?element.color?.replaceFirst('#', '0xff'):element.color) == selectedColor,
      orElse: () => SingleProductImages(id: "-1"),
    );

    imageToShow = filteredImage?.image;

    // لو مفيش صورة للون نفسه، نقدر ن fallback لصورة الـ variant
    if ((imageToShow == null || imageToShow!.isEmpty) && variantsForColor.first.id != null) {
      // ممكن توسّع هنا لو الـ API بيرجع صور داخل variant نفسه
    }

    emit(ProductsInitial());
  }

// اختيار اللون
  void setSelectedColor(int index,var selectedLocale) {
    selectedColorIndex = index;
    selectedColorValue = colorGroups.entries.elementAt(index).key;
    generateVariantAttrs(selectedLocale); // نحدث الـ attributes
    emit(ProductsInitial());
  }

// تكوين مجموعات الألوان
  void generateVariantColor(String locale) {
    colorGroups.clear();
    colorPatterns.clear();

    var variants = singleProduct?.data?.variants;
    if (variants != null) {
      for (var variant in variants) {
        String? colorAttr;
        String? patternUrl;

        for (var attr in variant.attributes ?? []) {
          if (attr.type == 'color' && attr.value != null) {
            colorAttr = attr.value;
            if (colorAttr?.contains('#')??false) {
              colorAttr = colorAttr?.replaceFirst('#', '0xff');
            }
          }
          if (attr.type == 'pattern' && attr.value != null) {
            patternUrl = attr.value;
          }
        }

        if (colorAttr != null) {
          colorGroups.putIfAbsent(colorAttr, () => []);
          colorGroups[colorAttr]!.add(variant);

          if (!colorPatterns.containsKey(colorAttr) && patternUrl != null) {
            colorPatterns[colorAttr] = patternUrl;
          }
        }
      }
    }

    emit(ProductsInitial());
  }

// توليد باقي الخصائص حسب اللون المختار
  void generateVariantAttrs(String locale) {
    otherAttributes.clear();
    if (selectedColorIndex == -1) return;

    final selectedColor = colorGroups.entries.elementAt(selectedColorIndex).key;
    final variantsForColor = colorGroups[selectedColor] ?? [];

    for (var variant in variantsForColor) {
      for (var attr in variant.attributes ?? []) {
        if (attr.type != 'color' && attr.type != 'pattern') {
          String attrName =
          locale == 'ar' ? (attr.nameArabic ?? '') : (attr.nameGerman ?? '');
          String attrValue = attr.value ?? '';
          otherAttributes.putIfAbsent(attrName, () => []);
          if (!otherAttributes[attrName]!.contains(attrValue)) {
            otherAttributes[attrName]!.add(attrValue);
          }
        }
      }
    }

    emit(ProductsInitial());
  }

// لما يختار المستخدم Attribute
  void setSelectedAttr(String attr, int index,String locale) {
    selectedAttributes = {};
    selectedAttributes[attr] = index;

    // نحاول نحدد الـ variantId الصحيح
    resolveSelectedVariantId(locale);

    emit(ProductsInitial());
  }

// فلترة لتحديد الـ VariantId الصحيح
  void resolveSelectedVariantId(String locale) {
    if (selectedColorIndex == -1) return;

    final selectedColor = colorGroups.entries.elementAt(selectedColorIndex).key;
    final variantsForColor = colorGroups[selectedColor] ?? [];

    for (var variant in variantsForColor) {
      bool match = true;

      for (var entry in selectedAttributes.entries) {
        final attrName = entry.key;
        final selectedIndex = entry.value;
        final selectedValue = otherAttributes[attrName]?[selectedIndex];

        final hasAttr = variant.attributes?.any((a) {
          final name = locale == 'ar' ? a.nameArabic : a.nameGerman;
          return name == attrName && a.value == selectedValue;
        }) ?? false;

        if (!hasAttr) {
          match = false;
          break;
        }
      }

      if (match) {
        // عيّن الـ VariantId
        selectedVariantId = variant.id.toString();
        print('Selected Variant ID: $selectedVariantId');

        // كمل باقي الـ attributes أوتوماتيك
        final attrs = variant.attributes ?? [];
        for (var a in attrs) {
          if (a.type != 'color' && a.type != 'pattern') {
            final attrName = (locale == 'ar' ?a.nameArabic : a.nameGerman)?.trim() ?? '';
            final attrValue = a.value ?? '';

            final valuesList = otherAttributes[attrName];
            if (valuesList != null) {
              final autoIndex = valuesList.indexOf(attrValue);
              if (autoIndex != -1) {
                selectedAttributes[attrName] = autoIndex;
              }
            }
          }
        }

        return; // خلاص وقف بعد أول match
      }
    }

    // لو مفيش match
    selectedVariantId = '';
    print('Selected Variant ID: $selectedVariantId');
  }


  // إعادة ضبط كل الاختيارات
  void resetVariantAttrs() {
    // اللون -> قائمة Variants
    colorGroups.clear();
    colorPatterns.clear();
    otherAttributes.clear();
    selectedAttributes.clear();

    selectedColorValue = '';
    selectedColorIndex = -1;
    selectedVariantId = '';
    imageToShow = null;

    emit(ProductsInitial());
  }
  void resetOtherAttrs() {

    selectedAttributes.clear();

    selectedColorValue = '';
    selectedColorIndex = -1;
    selectedVariantId = '';
    imageToShow = null;

    emit(ProductsInitial());
  }


  //###########################################################################
  bool isWishlistLoading = false;
  
  // Set to track product IDs currently being processed to prevent race conditions
  final Set<int> _wishlistOperationsInProgress = {};

  setIsWishlistLoadingTrue() {
    isWishlistLoading = true;

    emit(ProductsInitial());
  }

  setIsWishlistLoadingFalse() {
    isWishlistLoading = false;
    emit(ProductsInitial());
  }

  int selectedId = -1;

  setSelectedId(int id) {
    selectedId = id;
    emit(ProductsInitial());
  }

  removeSelectedId() {
    selectedId = -1;
    emit(ProductsInitial());
  }

  Future add2Wishlist({String? token, required int product_id}) async {
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

  Future deleteFromWishlist({String? token, required int itemId, required int product_id}) async {
    // Prevent duplicate operations for the same product
    if (_wishlistOperationsInProgress.contains(product_id)) {
      print('Wishlist delete operation already in progress for product: $product_id');
      return 'Operation in progress';
    }
    _wishlistOperationsInProgress.add(product_id);
    
    String apiUrl = '${Constants.apiBaseUrl}wishlist/$itemId';

    try {
      setSelectedId(product_id);
      // Send DELETE request to the API
      setIsWishlistLoadingTrue();
      final response = await http.delete(
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
      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          Helpers.wishlistItems[product_id] = false;
          Helpers.wishlistProducts.remove(product_id);
          _wishlistOperationsInProgress.remove(product_id);
          removeSelectedId();
          // Successful operation
          Helpers.showColoredToast(color: Colors.greenAccent, message: '${responseData['message']}');
          return null;
        }
      } else {
        _wishlistOperationsInProgress.remove(product_id);
        removeSelectedId();
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

  WishlistModel? wishlist;

  Future getWishlist({String? token}) async {
    String apiUrl = '${Constants.apiBaseUrl}wishlist';

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
      wishlist = WishlistModel.fromJson(responseData);
      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (responseData['status'] == true) {
          print("daaaaaaaaaaaaaaaaaaaaaaaaaaa:: ${wishlist?.data?.length}");
          for (WishlistItemData? item in wishlist?.data ?? []) {
            Helpers.wishlistItems[item?.productId ?? 0] = true;
            Helpers.wishlistProducts[item?.productId ?? 0] = item?.id??0;
            emit(ProductsInitial());
          }
          // Successful login
          // Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Wishlist Successfully!');
          return null;
        }
      } else {
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
