class VendorProductsModel {
  bool? status;
  List<VendorProduct>? data;

  VendorProductsModel({this.status, this.data});

  VendorProductsModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    if (json['data'] != null) {
      data = <VendorProduct>[];
      json['data'].forEach((v) {
        data!.add(VendorProduct.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['status'] = status;
    if (this.data != null) {
      data['data'] = this.data!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class VendorProduct {
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
  String? minPrice;
  int? minPriceVariantId;
  List<Images>? images;
  Category? category;

  VendorProduct(
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
        this.minPrice,
        this.minPriceVariantId,
        this.images,
        this.category});

  VendorProduct.fromJson(Map<String, dynamic> json) {
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
    minPrice = json['min_price']?.toString();
    minPriceVariantId = json['min_price_variant_id'];
    if (json['images'] != null) {
      images = <Images>[];
      json['images'].forEach((v) {
        images!.add(Images.fromJson(v));
      });
    }
    category = json['category'] != null
        ? Category.fromJson(json['category'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name_arabic'] = nameArabic;
    data['name_german'] = nameGerman;
    data['slug_arabic'] = slugArabic;
    data['slug_german'] = slugGerman;
    data['description_arabic'] = descriptionArabic;
    data['description_german'] = descriptionGerman;
    data['category_id'] = categoryId;
    data['is_active'] = isActive;
    data['vendor_id'] = vendorId;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['min_price'] = minPrice;
    data['min_price_variant_id'] = minPriceVariantId;
    if (images != null) {
      data['images'] = images!.map((v) => v.toJson()).toList();
    }
    if (category != null) {
      data['category'] = category!.toJson();
    }
    return data;
  }
}

class Images {
  int? id;
  int? productId;
  String? image;
  String? createdAt;
  String? updatedAt;

  Images({this.id, this.productId, this.image, this.createdAt, this.updatedAt});

  Images.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    productId = json['product_id'];
    image = json['image'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['product_id'] = productId;
    data['image'] = image;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}

class Category {
  int? id;
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? image;
  int? parentId;
  String? createdAt;
  String? updatedAt;

  Category(
      {this.id,
        this.nameArabic,
        this.nameGerman,
        this.slugArabic,
        this.slugGerman,
        this.image,
        this.parentId,
        this.createdAt,
        this.updatedAt});

  Category.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    nameArabic = json['name_arabic'];
    nameGerman = json['name_german'];
    slugArabic = json['slug_arabic'];
    slugGerman = json['slug_german'];
    image = json['image'];
    parentId = json['parent_id'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name_arabic'] = nameArabic;
    data['name_german'] = nameGerman;
    data['slug_arabic'] = slugArabic;
    data['slug_german'] = slugGerman;
    data['image'] = image;
    data['parent_id'] = parentId;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}