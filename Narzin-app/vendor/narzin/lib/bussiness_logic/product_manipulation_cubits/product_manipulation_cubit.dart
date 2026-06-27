import 'dart:convert';
import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_colorpicker/flutter_colorpicker.dart';
import 'package:flutter_decorated_container/flutter_decorated_container.dart';
import 'package:http/http.dart' as http;
import 'package:image_picker/image_picker.dart';
import 'package:logger/logger.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/model_layer/add_product_model.dart';
import 'package:narzin/model_layer/attributes_model.dart';
import 'package:narzin/model_layer/categories_model.dart';
import 'package:narzin/model_layer/products_model.dart';
import 'package:narzin/model_layer/single_produt_model.dart';
import 'package:narzin/model_layer/update_product_model.dart';
import 'package:narzin/widgets/text_form_fields/custom_input_decorator.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field.dart';

// import 'package:quill_html_editor/quill_html_editor.dart';

import '../../core/constants.dart';
import '../../core/screen_sizing_constants.dart';
import '../../generated/l10n.dart';
import '../../widgets/buttons/custom_main_buttons.dart';
import '../localization_cubit/localization_cubit.dart';

part 'product_manipulation_state.dart';

class ProductManipulationCubit extends Cubit<ProductManipulationState> {
  ProductManipulationCubit() : super(ProductManipulationInitial());

  AddingProductModel? productToPost;

  final ImagePicker _imagePicker = ImagePicker();
  File? image;
  File? patternImage;
  List<File> images = [];
  List<String> imagesPaths = [];
  TextEditingController arabicName = TextEditingController();
  TextEditingController germanName = TextEditingController();
  TextEditingController germanDesc = TextEditingController();
  TextEditingController arabicDesc = TextEditingController();
  TextEditingController hexInput = TextEditingController();
  TextEditingController weight = TextEditingController();

  // QuillEditorController  arabicDescHtml = QuillEditorController();
  // QuillEditorController  germanDescHtml = QuillEditorController();

  // TextEditingController size = TextEditingController();

  List<Widget> attributesForm = [];
  final TextEditingController price = TextEditingController();
  final TextEditingController cost = TextEditingController();
  final TextEditingController tax = TextEditingController();
  final TextEditingController stock = TextEditingController();
  final TextEditingController expiryDays = TextEditingController();
  DateTime? pickedExpiryDate;
  bool isColorSelected = false;

  // Attribute Controllers
  final Map<String, Map<String, dynamic>> attrsControllers = {};
  final Map<String, String?> selectedVals = {};

  // Data Storage
  final List<AttributesToPost> attributesToPost = [];
  final List<VariantsToPost> variantsToPost = [];
  final List<VariantsToPost> variantsToUpdate = [];

  // Reset the basic product form
  void resetBasicForm() {
    arabicDesc = TextEditingController();
    arabicName = TextEditingController();
    germanName = TextEditingController();
    germanDesc = TextEditingController();
    weight = TextEditingController();
    // germanDescHtml = QuillEditorController();
    // arabicDescHtml = QuillEditorController();
    image = null;

    images.clear();
    imagesToDelete.clear();
    imagesPaths.clear();
    selectedCategory = null;
  }

  // Reset the variant form
  void resetVariantsForm() {
    selectedVariantIndex = -1;
    attributesToPost.clear();
    isColorSelected = false;
    price.clear();
    stock.clear();
    cost.clear();
    tax.clear();
    expiryDays.clear();
    pickedExpiryDate = null;

    attrsControllers.forEach((key, value) {
      value.forEach((id, _) {
        if (key == "color") {
          value[id] = const Color(0xffffffff);
        } else if (key == "text") {
          value[id] = TextEditingController();
        } else if (key == "select") {
          selectedVals[id] = null;
        } else if (key == "pattern") {
          value[id] = null;
        }
      });
    });

    attributesToPost.clear();
    emit(ProductManipulationInitial());
  }

  bool isExpiryDaysSelected = false;
  bool isExpiryDateSelected = false;

  setIsExpiryDaysSelected() {
    if (expiryDays.text.isNotEmpty) {
      isExpiryDaysSelected = true;
    } else {
      isExpiryDaysSelected = false;
    }

    emit(ProductManipulationInitial());
  }

  setIsExpiryDateSelected() {
    if (pickedExpiryDate != null) {
      isExpiryDateSelected = true;
    } else {
      isExpiryDateSelected = false;
    }
    emit(ProductManipulationInitial());
  }

  // Reset stock based on inputs
  bool resetStock() {
    if (expiryDays.text.isNotEmpty) {
      stock.clear();
      pickedExpiryDate = null;
    } else if (pickedExpiryDate != null) {
      expiryDays.clear();
      stock.clear();
    } else {
      return true;
    }

    emit(ProductManipulationInitial());
    return true;
  }

  // Generate attributes for posting
  Future<AttributesToPost?> generateAttributes() async {
    AttributesToPost? attrs;
    // print(attrsControllers);
    for (var attr in attrsControllers.entries) {
      if (attr.key == "color") {
        for (var val in attr.value.entries) {
          if (isColorSelected) {
            attrs = AttributesToPost(
              attributeId: int.tryParse(val.key),
              value: '0x${(val.value as Color).value.toRadixString(16).padLeft(8, '0').toUpperCase()}',
            );
            attributesToPost.add(attrs);
          }
        }
      } else if (attr.key == "text") {
        for (var val in attr.value.entries) {
          if ((val.value as TextEditingController).text.isNotEmpty) {
            attrs = AttributesToPost(attributeId: int.tryParse(val.key) ?? 0, value: (val.value as TextEditingController).text);
            attributesToPost.add(attrs);
          }
        }
      } else if (attr.key == "select") {
        for (var val in attr.value.entries) {
          if (selectedVals[val.key] != null) {
            attrs = AttributesToPost(attributeId: int.tryParse(val.key) ?? 0, value: selectedVals[val.key]);
            attributesToPost.add(attrs);
          }
        }
      } else if(attr.key == "pattern"){
        for (var val in attr.value.entries) {
          if (val.value != null) {
            attrs = AttributesToPost(
              attributeId: int.tryParse(val.key),
              value: Helpers.addDataUriHeader(val.value),
            );
            attributesToPost.add(attrs);
          }
        }
      }
    }
    return attrs;
  }

  int selectedVariantIndex = -1;

  void setSelectedVariantIndex(int index) {
    selectedVariantIndex = index;
    selectedExistedVariantIndex = -1;
    emit(ProductManipulationInitial());
  }

  deleteVariantIndex(int index){
    variantsToPost.removeAt(index);
    selectedVariantIndex = -1;
    selectedExistedVariantIndex = -1;
    emit(ProductManipulationInitial());
  }

  int selectedExistedVariantIndex = -1;

  void setSelectedExistedVariantIndex(int index) {
    selectedExistedVariantIndex = index;
    selectedVariantIndex = -1;
    emit(ProductManipulationInitial());
  }

  reGenerateAttributes(VariantsToPost variant) {
    for (var attr in attrsControllers.entries) {
      if (attr.key == 'color') {
        isColorSelected = true;
        for (var val in attr.value.entries) {
          var attribute = variant.attributes?.firstWhere(
            (element) => element.attributeId == int.tryParse(val.key),
          );
          attrsControllers[attr.key]?[val.key] = Color(int.tryParse(attribute?.value ?? '0xfffffffff') ?? 0xffffffff);
        }
      } else if (attr.key == 'text') {
        for (var val in attr.value.entries) {
          var attribute = variant.attributes?.firstWhere(
            (element) => element.attributeId == int.tryParse(val.key),
          );
          attrsControllers[attr.key]?[val.key].text = attribute?.value;
        }
      } else if (attr.key == 'select') {
        for (var val in attr.value.entries) {
          var attribute = variant.attributes?.firstWhere(
            (element) => element.attributeId == int.tryParse(val.key),
          );
          selectedVals[val.key] = attribute?.value;
        }
      } else if (attr.key == 'pattern') {
        for (var val in attr.value.entries) {
          var attribute = variant.attributes?.firstWhere(
                (element) => element.attributeId == int.tryParse(val.key),
          );
          attrsControllers[attr.key]?[val.key] = Helpers.addDataUriHeader(attribute?.value??'');
        }
      }
    }
    stock.text = variant.stock.toString();
    price.text = variant.price.toString();
    cost.text = variant.cost.toString();
    tax.text = variant.tax.toString();
    expiryDays.text = variant.expiryDays.toString();

    // print("##########################################");
    // print(attrsControllers);
    // print("############################################");
    emit(ProductManipulationInitial());
  }

  // Formulate and add a new variant
  void formulateVariant() {
    generateAttributes();

    final totalDays = pickedExpiryDate != null ? pickedExpiryDate!.millisecondsSinceEpoch ~/ Duration.millisecondsPerDay : 0;
    List<AttributesToPost> deepCopiedAttributes = attributesToPost.map((attr) {
      return AttributesToPost(attributeId: attr.attributeId, value: attr.value);
    }).toList();
    final variant = VariantsToPost(
      color_tag_id: 1,
      attributes: deepCopiedAttributes,
      expiryDays: expiryDays.text.isNotEmpty ? int.tryParse(expiryDays.text) ?? 0 : totalDays,
      price: double.tryParse(price.text)??0,
      stock: int.tryParse(stock.text) ?? 0,
      cost: double.tryParse(cost.text) ?? 0,
      tax: double.tryParse(tax.text) ?? 0,
    );

    variantsToPost.add(variant);
    resetVariantsForm();

    // Debugging
    // print(jsonEncode(attributesToPost.map((v) => v.toJson()).toList()));
    // print(jsonEncode(variantsToPost.map((v) => v.toJson()).toList()));

    Helpers.showColoredToast(
      message: 'Attributes Added successfully.',
      color: Colors.greenAccent,
    );

    emit(ProductManipulationInitial());
  }

  void formulateIndexedVariant(int index) {
    generateAttributes();

    final totalDays = pickedExpiryDate != null ? pickedExpiryDate!.millisecondsSinceEpoch ~/ Duration.millisecondsPerDay : 0;
    List<AttributesToPost> deepCopiedAttributes = attributesToPost.map((attr) {
      return AttributesToPost(attributeId: attr.attributeId, value: attr.value);
    }).toList();
    final variant = VariantsToPost(
      color_tag_id: 1,
      attributes: deepCopiedAttributes,
      expiryDays: expiryDays.text.isNotEmpty ? int.tryParse(expiryDays.text) ?? 0 : totalDays,
      price: double.tryParse(price.text)??0,
      stock: int.tryParse(stock.text) ?? 0,
      cost: double.tryParse(cost.text) ?? 0,
      tax: double.tryParse(tax.text) ?? 0,
    );

    variantsToPost.removeAt(index);
    variantsToPost.insert(index, variant);
    resetVariantsForm();

    // Debugging
    // print(jsonEncode(attributesToPost.map((v) => v.toJson()).toList()));
    // print(jsonEncode(variantsToPost.map((v) => v.toJson()).toList()));

    Helpers.showColoredToast(
      message: 'Attributes Added successfully.',
      color: Colors.greenAccent,
    );

    emit(ProductManipulationInitial());
  }

  AttributesModel? attributes;

  // Initialize attributes form
  void initializeAttributesForm() {
    if (attributes?.status == true) {
      attributes?.data?.forEach((attr) {
        switch (attr.type) {
          case 'text':
            attrsControllers['text'] ??= {};
            attrsControllers['text']![attr.id.toString()] = TextEditingController();
            break;
          case 'color':
            attrsControllers['color'] ??= {};
            attrsControllers['color']![attr.id.toString()] = const Color(0xffffffff);
            break;
          case 'select':
            selectedVals[attr.id.toString()] = null;
            attrsControllers['select'] ??= {};
            attrsControllers['select']![attr.id.toString()] = attr.typeValues;
            break;
          case 'pattern':
            attrsControllers['pattern'] ??= {};
            attrsControllers['pattern']![attr.id.toString()] = null;
            break;
        }
      });

      Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Attributes Successfully!');
      emit(ProductManipulationInitial());
    }
  }

  // Build the form
  List<Widget> buildForm(BuildContext context) {
    attributesForm.clear();

    attributes?.data?.forEach((attr) {
      final locale = BlocProvider.of<LocalizationCubit>(context).locale;
      final name = locale == 'ar' ? attr.nameArabic ?? '' : attr.nameGerman ?? '';

      if (attr.type == 'text') {
        attributesForm.add(CustomTextFormField(
          title: name,
          controller: attrsControllers['text']?[attr.id.toString()],
          hint: '',
        ));
      } else if (attr.type == 'color') {
        attributesForm.add(InkWell(
          onTap: () {
            _pickColor(context, attr.id.toString(), name);
          },
          child: _colorPickerWidget(attr.id.toString(), name, context),
        ));
      } else if (attr.type == 'select') {
        attributesForm.add(
          CustomInputDecorator(
            title: name,
            child: Container(height: 40, child: _buildDropdown(attr.id.toString(), attrsControllers['select']?[attr.id.toString()] ?? [])),
          ),
        );
      } else if(attr.type == 'pattern'){
        attributesForm.add(
            _buildImagePicker(context, attr.id.toString(), name),
        );
      }

      attributesForm.add(const SizedBox(height: 20));
    });
    // print("FFFFFFFFFFORM:::  $attributesForm");

    return attributesForm;
  }

  // Helper for color picker
  void _pickColor(BuildContext context, String id, String title) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        content: SingleChildScrollView(
          child: Column(
            children: [
              ColorPicker(
                hexInputController: hexInput,
                pickerAreaBorderRadius: BorderRadius.circular(20),
                paletteType: PaletteType.hsl,
                pickerColor: attrsControllers['color']?[id],
                onColorChanged: (value) {
                  attrsControllers['color']?[id] = value;
                  isColorSelected = true;
                  emit(ProductManipulationInitial());
                },
              ),
              CustomTextFormField(title: S.of(context).paste_color, hint: '#ff000000....',controller: hexInput,)
            ],
          ),
        ),
        actions: [
          CustomSignIn_UpOne(
            title: S.of(context).choose_color,
            ontap: () {
              Clipboard.setData(ClipboardData(text: hexInput.text));
              Navigator.pop(context);
            },
          ),
        ],
      ),
      barrierDismissible: false,
    );
  }

  // Helper for dropdown
  Widget _buildDropdown(String id, List<String?> options) {
    return DropdownButton<String?>(
      hint: Text(selectedVals[id] ?? ''),
      isExpanded: true,
      padding: EdgeInsets.zero,
      items: options.map((e) => DropdownMenuItem(value: e, child: Text(e ?? ''))).toList(),
      value: selectedVals[id],
      underline: SizedBox(),
      onChanged: (value) {
        selectedVals[id] = value;
        emit(ProductManipulationInitial());
      },
    );
  }

  Widget _buildImagePicker(BuildContext context, String id, String? title, ) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,

      children: [
        if (title != null) // Only show title if not null
          Text(
            title,
            style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 15),
          ),
        SizedBox(height: 10,),
        InkWell(
          onTap: () async {
            attrsControllers['pattern']?[id] = await pickPatternImageFromGallery();
            // emit(ProductManipulationInitial());
          },
          child: DecoratedContainer(
            strokeWidth: 1,
            dashSpace: 4,
            dashWidth: 6,
            cornerRadius: 16,
            strokeColor: Colors.grey,
            child: Container(
              width: ScreenSizing.width,
              height: 100,
              child: attrsControllers['pattern']?[id] != null
                  ? ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Image.memory(
                    Helpers.decodeBase64Image(attrsControllers['pattern']?[id]),
                    fit: BoxFit.cover,
                  ))
                  : Row(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  const Icon(
                    Icons.perm_media_outlined,
                    size: 20,
                  ),
                  const SizedBox(
                    width: 10,
                  ),
                  Text(S.of(context).product_media),
                ],
              ),
            ),
          ),
        ),
      ],
    );
  }

  // Helper for color display widget
  Widget _colorPickerWidget(String id, String title, context) {
    return CustomInputDecorator(
      title: title,
      suffix: Padding(
        padding: const EdgeInsets.all(8.0),
        child: CircleAvatar(radius: 5, backgroundColor: attrsControllers['color']?[id]),
      ),
      child: Text(
        isColorSelected ? '#${(attrsControllers['color']?[id] as Color).value.toRadixString(16).padLeft(8, '0').toUpperCase()}' : S.of(context).choose_color,
      ),
    );
  }

  addImage(File? imageFile) {
    if (imageFile != null) {
      images.add(imageFile);
      imagesPaths.add(imageFile.path);
    }
    emit(ProductManipulationInitial());
  }

  List<String>? items = [
    'xxxx1',
    'xxxxxx2',
    'xxx3x',
    'xxxxx4',
  ];
  String? SelectedItem;
  DateTime? pickedDate;

  Future<void> selectExpiryDate(BuildContext context) async {
    pickedExpiryDate = await showDatePicker(
      context: context,
      initialDate: pickedExpiryDate, // Default date is the current state or now
      firstDate: DateTime(DateTime.now().year), // Earliest date selectable
      lastDate: DateTime(2100),
      // Latest date selectable
    );
    if (pickedExpiryDate != null) {
      emit(ProductManipulationInitial()); // Update the state with the selected date
    }
  }

  Future<void> selectDate(BuildContext context) async {
    pickedDate = await showDatePicker(
      context: context,
      initialDate: pickedDate ?? DateTime.now(), // Default date is the current state or now
      firstDate: DateTime(DateTime.now().year), // Earliest date selectable
      lastDate: DateTime(2100), // Latest date selectable
    );
    if (pickedDate != null) {
      emit(ProductManipulationInitial()); // Update the state with the selected date
    }
  }

  setSelectedItem(String? val) {
    SelectedItem = val;
    emit(ProductManipulationInitial());
  }

  /// Pick an image from the gallery
  Future<void> pickImageFromGallery() async {
    image = null;

    try {
      final XFile? pickedFile = await _imagePicker.pickImage(source: ImageSource.gallery);

      if (pickedFile != null) {
        image = File(pickedFile.path);
        addImage(image);
        emit(ProductImagePickedSuccess(image!));
      } else {
        emit(ProductImageError("No image selected."));
      }
    } catch (e) {
      emit(ProductImageError("Failed to pick image: $e"));
    }
  }

  Future<String?> pickPatternImageFromGallery() async {
    File? image;
    try {
      final XFile? pickedFile = await _imagePicker.pickImage(source: ImageSource.gallery);

      if (pickedFile != null) {
        image = File(pickedFile.path);
        var base = await Helpers.encodeFileToBase64(image.path);
        emit(ProductImagePickedSuccess(image));

        return base;
      } else {
        emit(ProductImageError("No image selected."));
      }
    } catch (e) {
      emit(ProductImageError("Failed to pick image: $e"));
    }
  }

  /// Pick an image from the camera
  Future<void> pickImageFromCamera() async {
    image = null;
    try {
      final XFile? pickedFile = await _imagePicker.pickImage(source: ImageSource.camera);

      if (pickedFile != null) {
        image = File(pickedFile.path);
        emit(ProductImagePickedSuccess(image!));
      } else {
        emit(ProductImageError("No image captured."));
      }
    } catch (e) {
      emit(ProductImageError("Failed to capture image: $e"));
    }
  }

  bool isLoading = false;

  setIsLoadingTrue() {
    isLoading = true;
    emit(ProductManipulationInitial());
  }

  setIsLoadingFalse() {
    isLoading = false;
    emit(ProductManipulationInitial());
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
      // print(response.body);
      // print(response.statusCode);
      // Parse the JSON response using the LoginModel
      final Map<String, dynamic> responseData = json.decode(response.body);
      products = ProductsModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (products?.status == true) {
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

      Helpers.showColoredToast(color: Colors.red, message: 'Unexpected Error: Status Code ${response.statusCode}: ${products?.message ?? ''}');

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
  CategoryData? selectedCategory;
  List<SubCategories> subCategories = [];
  SubCategories? selectedSubCategory;

  setSelectedSubCategory(SubCategories? val) {
    selectedSubCategory = val;
    emit(ProductManipulationInitial());
  }

  setSelectedCategory(CategoryData? val) {
    selectedSubCategory = null;
    selectedCategory = val;
    subCategories = val?.subCategories ?? [];
    emit(ProductManipulationInitial());
  }

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

  Future getAttributes() async {
    String apiUrl = '${Constants.apiBaseUrl}attributes';

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
      attributes = AttributesModel.fromJson(responseData);

      // Handle response
      if (response.statusCode == 200 || response.statusCode == 201) {
        if (attributes?.status == true) {
          // Successful login
          // print('ssssssssssssssssssssssssssssss');
          Helpers.showColoredToast(color: Colors.greenAccent, message: 'Got Attributes Successfully!');
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
      // print('ssssssssssssssssssssssssssssss${e.toString()}');

      // Handle network or other exceptions
      return 'An error occurred: $e';
    }
  }



  Map<String, String> colorImages = {};

  void setColorsImages(String color, String imagePath) {
    colorImages[color] = imagePath;
    emit(ProductManipulationInitial());
  }

  deleteImage(String key) {
    if (colorImages.containsKey(key)) {
      colorImages.remove(key);
    }
    emit(ProductManipulationInitial());
  }

  Future postProductImages({
    required String token,
    String? productId,
  }) async {
    String apiUrl = '${Constants.apiBaseUrl}products/images/${productId ?? tempProductId}';

    try {
      setIsLoadingTrue();

      // Convert images to base64 and prepare the body
      List<Map<String, dynamic>> imagesList = [];

      for (var entry in colorImages.entries) {
        String color = entry.key;
        String imagePath = entry.value;

        String base64Image = await Helpers.imageToBase64(imagePath, withMime: true);

        imagesList.add({
          'image': base64Image,
          'color': color,
        });
      }

      Map<String, dynamic> body = {
        'images': imagesList,
      };

      String jsonBody = jsonEncode(body);
      // print('Sending JSON Body: $jsonBody');

      // Send the request
      final response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
        },
        body: jsonBody,
      );

      setIsLoadingFalse();

      // print('Status Code: ${response.statusCode}');
      // print('Response Body: ${response.body}');
      Logger().t(response.body);

      final Map<String, dynamic> responseData = jsonDecode(response.body);

      if (responseData['status'] == true) {
        // print('Success: ${responseData['message']}');
        Helpers.showColoredToast(
          color: Colors.greenAccent,
          message: responseData['message'],
        );
        return '';
      } else {
        String errorMessage = '';
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
        } else {
          errorMessage = 'Unexpected Error';
        }
        // print('Error: $errorMessage');
        Helpers.showColoredToast(
          color: Colors.red,
          message: errorMessage,
        );
        return errorMessage;
      }
    } catch (e) {
      setIsLoadingFalse();
      // print('Error occurred: $e');
      Helpers.showColoredToast(
        color: Colors.red,
        message: 'An error occurred: $e',
      );
      return e.toString();
    }
  }


  List<int> imagesToDelete = [];

  setImagesToDelete(int index) {
    if (imagesToDelete.contains(index)) {
      imagesToDelete.remove(index);
    } else {
      imagesToDelete.add(index);
    }
    emit(ProductManipulationInitial());
  }

  Future deleteProductImages({required String token, String? productId}) async {
    String apiUrl = '${Constants.apiBaseUrl}products/images/$productId';

    try {
      // Create the product model
      setIsLoadingTrue();
      var response = await http.delete(
        Uri.parse(apiUrl),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
        },
        body: json.encode({
          "delete_images": [...imagesToDelete]
        }),
      );
      var logger = Logger();
      // Handle response
      final responseBody = response.body;
      setIsLoadingFalse();

      // print('Status Code: ${response.statusCode}');
      logger.t(responseBody);

      final Map<String, dynamic> responseData = jsonDecode(responseBody);

      if (responseData['status'] == true) {
        // print('Success: ${responseData['message']}');
        Helpers.showColoredToast(
          color: Colors.greenAccent,
          message: responseData['message'],
        );
        return null;
      } else {
        String errorMessage = '';
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
        } else {
          errorMessage = 'Unexpected Error';
        }

        // print('Error: $errorMessage');
        Helpers.showColoredToast(
          color: Colors.red,
          message: errorMessage,
        );
        return errorMessage;
      }
    } catch (e) {
      setIsLoadingFalse();
      // print('Error occurred: $e');
      Helpers.showColoredToast(
        color: Colors.red,
        message: 'An error occurred: $e',
      );
      return e.toString();
    }
  }

  int? tempProductId;

  Future postProduct({required String token}) async {
    tempProductId = null;
    String apiUrl = '${Constants.apiBaseUrl}products';

    try {
      // Create the product model
      AddingProductModel product = AddingProductModel(
        nameArabic: arabicName.text,
        nameGerman: germanName.text,
        descriptionArabic: arabicDesc.text,
        descriptionGerman: arabicDesc.text,
        categoryId: int.tryParse(selectedCategory?.id ?? '0'),
        child_category_id: int.tryParse(selectedSubCategory?.id ?? '0'),
        weight: weight.text,
        // images: imagesPaths, // Pass images as a list of file paths
        variants: variantsToPost,
      );
      final productJson = product.toJson();
      setIsLoadingTrue();
      var response = await http.post(
        Uri.parse(apiUrl),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
        },
        body: json.encode(productJson),
      );

      // Handle response
      final responseBody = response.body;
      setIsLoadingFalse();

      // print('Status Code: ${response.statusCode}');
      // logger.t(responseBody);

      final Map<String, dynamic> responseData = jsonDecode(responseBody);

      if (responseData['status'] == true) {
        // print('Success: ${responseData['message']}');
        tempProductId = int.tryParse(responseData['data']['id'].toString());
        Helpers.showColoredToast(
          color: Colors.greenAccent,
          message: responseData['message'],
        );
        return null;
      } else {
        String errorMessage = '';
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
        } else {
          errorMessage = 'Unexpected Error';
        }

        // print('Error: $errorMessage');
        Helpers.showColoredToast(
          color: Colors.red,
          message: errorMessage,
        );
        return errorMessage;
      }
    } catch (e) {
      setIsLoadingFalse();
      // print('Error occurred: $e');
      Helpers.showColoredToast(
        color: Colors.red,
        message: 'An error occurred: $e',
      );
      return e.toString();
    }
  }

  Future postProductWithDio({required String token}) async {
    String apiUrl = '${Constants.apiBaseUrl}products';
    final response;
    try {
      setIsLoadingTrue();

      final dio = Dio();
      dio.options.headers = {
        'Authorization': 'Bearer $token',
        'Content-Type': 'multipart/form-data',
      };

      // Serialize product fields
      final Map<String, dynamic> productFields = {
        'name_arabic': arabicName.text,
        'name_german': germanName.text,
        'description_arabic': arabicDesc.text,
        'description_german': germanDesc.text,
        'category_id': selectedCategory?.id.toString() ?? '',
        'weight': weight.text,
        'variants': jsonEncode(variantsToPost.map((v) {
          final variant = v.toJson();
          variant['expiry_days'] = variant['expiry_days'] == '0' ? null : variant['expiry_days'];
          return variant;
        }).toList()),
      };

      // Create FormData
      final formData = FormData.fromMap({
        ...productFields,
        'images[]': imagesPaths.map((imagePath) {
          return MultipartFile.fromFileSync(imagePath, filename: imagePath.split('/').last);
        }).toList(),
      });

      // print('Serialized Product: $productFields');
      // print('Sending Request: ${formData.fields}');
      // print('Uploading ${imagesPaths.length} images');

      response = await dio.post(apiUrl, data: formData);

      setIsLoadingFalse();
      // print('Status Code: ${response.statusCode}');
      // print('Response Data: ${response.data}');

      if (response.data['status'] == true) {
        Helpers.showColoredToast(color: Colors.greenAccent, message: response.data['message']);
        return null;
      } else {
        Helpers.showColoredToast(color: Colors.red, message: response.data['message'] + "\n" + Helpers.concatenateErrors(response.data['errors']).toString() ?? 'Unexpected error occurred');
        return Helpers.concatenateErrors(response.data['errors']).toString() ?? 'Unexpected error occurred';
      }
    } on DioError catch (e) {
      setIsLoadingFalse();

      if (e.response != null) {
        // print('Status Code: ${e.response?.statusCode}');
        // print('Response Data: ${e.response?.data}');
        Helpers.showColoredToast(color: Colors.red, message: e.response?.data['message'] + "\n" + Helpers.concatenateErrors(e.response?.data['errors']).toString() ?? 'Unexpected error occurred');
      } else {
        // print('Request Error: ${e.message}');
        Helpers.showColoredToast(color: Colors.red, message: 'An error occurred: ${e.message}');
      }
      return e.toString();
    }
  }

  //////////////////////////////[Update]///////////////////////////////////
  List<int> variantsToRemove = [];

  void AddVariantToDelete(int id) {
    if (variantsToRemove.contains(id)) return;
    variantsToRemove.add(id);
    emit(ProductManipulationInitial());
  }

  populateFieldsForUpdate(SingleProductModel product) async {
    if (product.data != null) {
      var productData = product.data!;

      // Populate general fields
      arabicName.text = productData.nameArabic ?? '';
      germanName.text = productData.nameGerman ?? '';
      arabicDesc.text = productData.descriptionArabic ?? '';
      germanDesc.text = productData.descriptionGerman ?? '';
      weight.text = productData.weight ?? '';
      final CategoryData defaultCategory = CategoryData(
        id: "-1", // Use a unique ID for the default category
        // Provide a default name
        // Add other required fields for the CategoryData class
      );
      if (categories?.data != null && productData.categoryId != null) {
        selectedCategory = categories!.data!.firstWhere(
          (cat) => cat.id == ((productData.category?.parentId) ?? (productData.category?.id)),
          orElse: () => defaultCategory, // Fallback if no match is found
        );
      } else {
        selectedCategory = null; // Handle null cases
      }
      if (selectedCategory?.id?.contains("-1") ?? false) {
        selectedCategory = null;
      }

      subCategories = selectedCategory?.subCategories ?? [];
      final SubCategories defaultSubCategory = SubCategories(
        id: "-1", // Use a unique ID for the default category
        // Provide a default name
        // Add other required fields for the CategoryData class
      );

      if (categories?.data != null && productData.categoryId != null && productData.category?.parentId != null) {
        selectedSubCategory = subCategories.firstWhere(
          (element) => element.id == productData.category?.id,
          orElse: () => defaultSubCategory,
        );
      } else {
        selectedSubCategory = null; // Handle null cases
      }
      if (selectedSubCategory?.id?.contains("-1") ?? false) {
        selectedSubCategory = null;
      }

      variantsToRemove.clear();
      variantsToPost.clear();

      // Populate variants
      variantsToUpdate.clear();
      productData.variants?.forEach((variant) async {
        String? base644;

        if(variant.attributes?.any((element) => element.attributeId == '4',)??false){
          base644 = await Helpers.imageUrlToBase64(variant.attributes?.firstWhere((element) => element.attributeId == '4',).value ??'');
        }

        String expiryDays = variant.expiryDays ?? '0';
        // print('###########################');
        // print(expiryDays);
        variantsToUpdate.add(VariantsToPost(
          id: int.tryParse(variant.id ?? ''),
          price: double.tryParse(variant.price ?? ''),
          stock: int.tryParse(variant.stock ?? ''),
          cost: 0,
          tax: 0,
          color_tag_id: int.tryParse(variant.colorTagId ?? "1"),
          expiryDays: int.tryParse(expiryDays) ?? 0,
          attributes: variant.attributes?.map((attr) {
            if(attr.attributeId == '4'){
              emit(ProductManipulationInitial());
              attr.value = base644;
            }
            // print('###########################${attr.value}');
            return AttributesToPost(attributeId: int.tryParse(attr.attributeId ?? '0'), value: attr.value); // Use IDs from the fetched attribute model if needed
          }).toList(),
        ));
      });

      emit(ProductManipulationInitial());
    }
    emit(ProductManipulationInitial());
  }

  Future updateProduct({required String token, required String productId}) async {
    String apiUrl = '${Constants.apiBaseUrl}products/$productId';

    try {
      if (variantsToRemove.isNotEmpty) {
        variantsToRemove.forEach((id) {
          variantsToUpdate.removeWhere((v) => v.id == id);
        });
      }
      // Create the product model
      UpdateProductModel product = UpdateProductModel(
          nameArabic: arabicName.text,
          nameGerman: germanName.text,
          descriptionArabic: arabicDesc.text,
          descriptionGerman: germanDesc.text,
          categoryId: int.tryParse(selectedCategory?.id ?? '0'),
          child_category_id: int.tryParse(selectedSubCategory?.id ?? '0'),
          weight: weight.text,
          // images: imagesPaths, // Pass images as a list of file paths
          variants: variantsToUpdate,
          newVariants: variantsToPost,
          deleteVariants: variantsToRemove,
          isActive: true);
      final productJson = product.toJson();
      Logger logger = Logger();
      logger.t(product.toJson());
      setIsLoadingTrue();
      var response = await http.put(
        Uri.parse(apiUrl),
        headers: {
          'Authorization': 'Bearer $token',
          'Content-Type': 'application/json',
        },
        body: json.encode(productJson),
      );
      // Handle response
      final responseBody = response.body;
      setIsLoadingFalse();

      logger.t(responseBody);

      final Map<String, dynamic> responseData = jsonDecode(responseBody);

      if (responseData['status'] == true) {
        tempProductId = int.tryParse(responseData['data']['id'].toString());
        Helpers.showColoredToast(
          color: Colors.greenAccent,
          message: responseData['message'],
        );
        return null;
      } else {
        String errorMessage = '';
        if (responseData['errors'] != null) {
          errorMessage = Helpers.concatenateErrors(responseData['errors']);
        } else {
          errorMessage = 'Unexpected Error';
        }

        Helpers.showColoredToast(
          color: Colors.red,
          message: errorMessage,
        );
        return errorMessage;
      }
    } catch (e) {
      setIsLoadingFalse();
      Helpers.showColoredToast(
        color: Colors.red,
        message: 'An error occurred: $e',
      );
      return e.toString();
    }
  }
}
