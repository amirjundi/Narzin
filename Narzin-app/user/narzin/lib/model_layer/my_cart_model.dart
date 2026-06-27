class MyCartModel {
  bool? status;
  String? message;
  List<Data>? data;

  MyCartModel({this.status, this.message, this.data});

  MyCartModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    if (json['data'] != null) {
      data = <Data>[];
      json['data'].forEach((v) {
        data!.add(Data.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['status'] = status;
    data['message'] = message;
    if (this.data != null) {
      data['data'] = this.data!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Data {
  String? id;
  String? userId;
  String? productId;
  String? productVariantId;
  String? quantity;
  String? createdAt;
  String? updatedAt;
  String? price;
  bool? outOfStock;
  Product? product;
  ProductVariant? productVariant;

  Data(
      {this.id,
        this.userId,
        this.productId,
        this.productVariantId,
        this.quantity,
        this.createdAt,
        this.updatedAt,
        this.price,
        this.outOfStock,
        this.product,
        this.productVariant});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    userId = json['user_id']?.toString();
    productId = json['product_id']?.toString();
    productVariantId = json['product_variant_id']?.toString();
    quantity = json['quantity']?.toString();
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    price = json['price']?.toString();
    outOfStock = json['out_of_stock'];
    product =
    json['product'] != null ? Product.fromJson(json['product']) : null;
    productVariant = json['product_variant'] != null
        ? ProductVariant.fromJson(json['product_variant'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['id'] = id;
    data['user_id'] = userId;
    data['product_id'] = productId;
    data['product_variant_id'] = productVariantId;
    data['quantity'] = quantity;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['price'] = price;
    data['out_of_stock'] = outOfStock;
    if (product != null) {
      data['product'] = product!.toJson();
    }
    if (productVariant != null) {
      data['product_variant'] = productVariant!.toJson();
    }
    return data;
  }
}

class Product {
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
  String? weight;
  List<Images>? images;

  Product(
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
        this.childCategoryId,
        this.averageRating,
        this.images});

  Product.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    nameArabic = json['name_arabic'];
    nameGerman = json['name_german'];
    slugArabic = json['slug_arabic'];
    slugGerman = json['slug_german'];
    descriptionArabic = json['description_arabic'];
    descriptionGerman = json['description_german'];
    categoryId = json['category_id']?.toString();
    isActive = json['is_active'];
    vendorId = json['vendor_id']?.toString();
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    childCategoryId = json['child_category_id']?.toString();
    averageRating = json['average_rating']?.toString();
    weight = json['weight']?.toString();
    if (json['images'] != null) {
      images = <Images>[];
      json['images'].forEach((v) {
        images!.add(Images.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
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
    if (images != null) {
      data['images'] = images!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Images {
  String? id;
  String? productId;
  String? image;
  String? url;

  Images({this.id, this.productId, this.image, this.url});

  Images.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    productId = json['product_id']?.toString();
    image = json['image'];
    url = json['url'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['id'] = id;
    data['product_id'] = productId;
    data['image'] = image;
    data['url'] = url;
    return data;
  }
}

class ProductVariant {
  String? id;
  String? productId;
  String? price;
  String? stock;
  String? expiryDate;
  String? expiryDays;
  String? sku;
  bool? isActive;
  bool? isOutOfStock;
  String? createdAt;
  String? updatedAt;
  String? colorTagId;
  String? cost;

  ProductVariant(
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
        this.colorTagId,
        this.cost});

  ProductVariant.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    productId = json['product_id']?.toString();
    price = json['price']?.toString();
    stock = json['stock']?.toString();
    expiryDate = json['expiry_date']?.toString();
    expiryDays = json['expiry_days']?.toString();
    sku = json['sku'];
    isActive = json['is_active'];
    isOutOfStock = json['is_out_of_stock'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    colorTagId = json['color_tag_id']?.toString();
    cost = json['cost']?.toString();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['id'] = id;
    data['product_id'] = productId;
    data['price'] = price;
    data['stock'] = stock;
    data['expiry_date'] = expiryDate;
    data['expiry_days'] = expiryDays;
    data['sku'] = sku;
    data['is_active'] = isActive;
    data['is_out_of_stock'] = isOutOfStock;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['color_tag_id'] = colorTagId;
    data['cost'] = cost;
    return data;
  }
}