class AddingProductResponseModel {
  bool? status;
  String? message;
  Data? data;

  AddingProductResponseModel({this.status, this.message, this.data});

  AddingProductResponseModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
  }

}

class Data {
  int? id;
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? descriptionArabic;
  String? descriptionGerman;
  int? categoryId;
  bool? isActive;
  int? vendorId;
  String? createdAt;
  String? updatedAt;
  List<Variants>? variants;
  List<String>? images;

  Data(
      {this.id,
        this.nameArabic,
        this.nameGerman,
        this.slugArabic,
        this.slugGerman,
        this.descriptionArabic,
        this.descriptionGerman,
        this.categoryId,
        this.isActive,
        this.vendorId,
        this.createdAt,
        this.updatedAt,
        this.variants,
        this.images});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    nameArabic = json['name_arabic'];
    nameGerman = json['name_german'];
    slugArabic = json['slug_arabic'];
    slugGerman = json['slug_german'];
    descriptionArabic = json['description_arabic'];
    descriptionGerman = json['description_german'];
    categoryId = json['category_id'];
    isActive = json['is_active'];
    vendorId = json['vendor_id'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    if (json['variants'] != null) {
      variants = <Variants>[];
      json['variants'].forEach((v) {
        variants!.add(Variants.fromJson(v));
      });
    }
    if (json['images'] != null) {
      images = <String>[];
      json['images'].forEach((v) {
        images!.add(v);
      });
    }
  }


}

class Variants {
  int? id;
  int? productId;
  String? price;
  int? stock;
  String? expiryDate;
  String? expiryDays;
  String? sku;
  bool? isActive;
  bool? isOutOfStock;
  String? createdAt;
  String? updatedAt;
  List<VariantValues>? variantValues;

  Variants(
      {this.id,
        this.productId,
        this.price,
        this.stock,
        this.expiryDate,
        this.expiryDays,
        this.sku,
        this.isActive,
        this.isOutOfStock,
        this.createdAt,
        this.updatedAt,
        this.variantValues});

  Variants.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    productId = json['product_id'];
    price = json['price'];
    stock = json['stock'];
    expiryDate = json['expiry_date'];
    expiryDays = json['expiry_days'];
    sku = json['sku'];
    isActive = json['is_active'];
    isOutOfStock = json['is_out_of_stock'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    if (json['variant_values'] != null) {
      variantValues = <VariantValues>[];
      json['variant_values'].forEach((v) {
        variantValues!.add(VariantValues.fromJson(v));
      });
    }
  }
}

class VariantValues {
  int? id;
  int? productVariantsId;
  int? variantAttributeId;
  String? value;
  String? createdAt;
  String? updatedAt;

  VariantValues(
      {this.id,
        this.productVariantsId,
        this.variantAttributeId,
        this.value,
        this.createdAt,
        this.updatedAt});

  VariantValues.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    productVariantsId = json['product_variants_id'];
    variantAttributeId = json['variant_attribute_id'];
    value = json['value'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }
}