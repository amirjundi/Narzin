String? _asString(dynamic v) => v?.toString();

class SingleProductModel {
  bool? status;
  String? message;
  Data? data;

  SingleProductModel({this.status, this.data, this.message});

  SingleProductModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = _asString(json['message']);
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['status'] = status;
    data['message'] = message;
    if (this.data != null) {
      data['data'] = this.data!.toJson();
    }
    return data;
  }
}

class Data {
  String? id;
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? descriptionArabic;
  String? descriptionGerman;
  String? categoryId;
  bool? isActive;
  String? vendorId;
  String? createdAt;
  String? updatedAt;
  String? childCategoryId;
  String? averageRating;
  List<SingleProductVariants>? variants;
  List<Images>? images;
  Category? category;
  String? weight;

  Data({
    this.id,
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
    this.childCategoryId,
    this.averageRating,
    this.weight,
    this.variants,
    this.images,
    this.category,
  });

  Data.fromJson(Map<String, dynamic> json) {
    id = _asString(json['id']);
    nameArabic = _asString(json['name_arabic']);
    nameGerman = _asString(json['name_german']);
    slugArabic = _asString(json['slug_arabic']);
    slugGerman = _asString(json['slug_german']);
    descriptionArabic = _asString(json['description_arabic']);
    descriptionGerman = _asString(json['description_german']);
    categoryId = _asString(json['category_id']);
    isActive = json['is_active'];
    vendorId = _asString(json['vendor_id']);
    createdAt = _asString(json['created_at']);
    updatedAt = _asString(json['updated_at']);
    childCategoryId = _asString(json['child_category_id']);
    averageRating = _asString(json['average_rating']);
    weight = _asString(json['weight']);

    if (json['variants'] != null) {
      variants = <SingleProductVariants>[];
      json['variants'].forEach((v) {
        variants!.add(SingleProductVariants.fromJson(v));
      });
    }

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
    data['child_category_id'] = childCategoryId;
    data['average_rating'] = averageRating;
    data['weight'] = weight;

    if (variants != null) {
      data['variants'] = variants!.map((v) => v.toJson()).toList();
    }
    if (images != null) {
      data['images'] = images!.map((v) => v.toJson()).toList();
    }
    if (category != null) {
      data['category'] = category!.toJson();
    }
    return data;
  }
}

class SingleProductVariants {
  String? id;
  String? price;
  String? stock;
  String? colorTagId;
  String? sku;
  bool? isActive;
  bool? isOutOfStock;
  String? expiryDate;
  String? expiryDays;
  List<Attributes>? attributes;

  SingleProductVariants({
    this.id,
    this.price,
    this.stock,
    this.colorTagId,
    this.sku,
    this.isActive,
    this.isOutOfStock,
    this.expiryDate,
    this.expiryDays,
    this.attributes,
  });

  SingleProductVariants.fromJson(Map<String, dynamic> json) {
    id = _asString(json['id']);
    price = _asString(json['price']);
    stock = _asString(json['stock']);
    colorTagId = _asString(json['color_tag_id']);
    sku = _asString(json['sku']);
    isActive = json['is_active'];
    isOutOfStock = json['is_out_of_stock'];
    expiryDate = _asString(json['expiry_date']);
    expiryDays = _asString(json['expiry_days']);

    if (json['attributes'] != null) {
      attributes = <Attributes>[];
      json['attributes'].forEach((v) {
        attributes!.add(Attributes.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['price'] = price;
    data['stock'] = stock;
    data['color_tag_id'] = colorTagId;
    data['sku'] = sku;
    data['is_active'] = isActive;
    data['is_out_of_stock'] = isOutOfStock;
    data['expiry_date'] = expiryDate;
    data['expiry_days'] = expiryDays;

    if (attributes != null) {
      data['attributes'] = attributes!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Attributes {
  String? attributeId;
  String? nameArabic;
  String? nameGerman;
  String? value;

  Attributes({
    this.attributeId,
    this.nameArabic,
    this.nameGerman,
    this.value,
  });

  Attributes.fromJson(Map<String, dynamic> json) {
    attributeId = _asString(json['attribute_id']);
    nameArabic = _asString(json['name_arabic']);
    nameGerman = _asString(json['name_german']);
    value = _asString(json['value']);
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['attribute_id'] = attributeId;
    data['name_arabic'] = nameArabic;
    data['name_german'] = nameGerman;
    data['value'] = value;
    return data;
  }
}

class Images {
  String? id;
  String? productId;
  String? image;
  String? url;

  Images({
    this.id,
    this.productId,
    this.image,
    this.url,
  });

  Images.fromJson(Map<String, dynamic> json) {
    id = _asString(json['id']);
    productId = _asString(json['product_id']);
    image = _asString(json['image']);
    url = _asString(json['url']);
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['product_id'] = productId;
    data['image'] = image;
    data['url'] = url;
    return data;
  }
}

class Category {
  String? id;
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? image;
  String? parentId;
  String? createdAt;
  String? updatedAt;

  Category({
    this.id,
    this.nameArabic,
    this.nameGerman,
    this.slugArabic,
    this.slugGerman,
    this.image,
    this.parentId,
    this.createdAt,
    this.updatedAt,
  });

  Category.fromJson(Map<String, dynamic> json) {
    id = _asString(json['id']);
    nameArabic = _asString(json['name_arabic']);
    nameGerman = _asString(json['name_german']);
    slugArabic = _asString(json['slug_arabic']);
    slugGerman = _asString(json['slug_german']);
    image = _asString(json['image']);
    parentId = _asString(json['parent_id']);
    createdAt = _asString(json['created_at']);
    updatedAt = _asString(json['updated_at']);
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
