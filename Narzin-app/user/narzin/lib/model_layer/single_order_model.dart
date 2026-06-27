class SingleOrderModel {
  bool? status;
  Data? data;
  String? message;

  SingleOrderModel({this.status, this.data, this.message});

  SingleOrderModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
  }
}

class Data {
  int? id;
  int? userId;
  int? addressId;
  String? orderNumber;
  String? totalAmount;
  String? priceAfterDiscount;
  String? walletUsage;
  String? finalPrice;
  String? paymentStatus;
  String? orderStatus;
  String? notes;
  int? couponId;
  String? shippingType;
  String? shippingCost;
  String? createdAt;
  String? updatedAt;
  int? statusId;
  List<Items>? items;
  Address? address;

  Data(
      {this.id,
        this.userId,
        this.addressId,
        this.orderNumber,
        this.totalAmount,
        this.priceAfterDiscount,
        this.walletUsage,
        this.finalPrice,
        this.paymentStatus,
        this.orderStatus,
        this.notes,
        this.couponId,
        this.shippingType,
        this.shippingCost,
        this.createdAt,
        this.updatedAt,
        this.statusId,
        this.items,
        this.address});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    userId = json['user_id'];
    addressId = json['address_id'];
    orderNumber = json['order_number'];
    totalAmount = json['total_amount']?.toString();
    priceAfterDiscount = json['price_after_discount']?.toString();
    walletUsage = json['wallet_usage']?.toString();
    finalPrice = json['final_price']?.toString();
    paymentStatus = json['payment_status'];
    orderStatus = json['order_status'];
    notes = json['notes'];
    couponId = json['coupon_id'];
    shippingType = json['shipping_type'];
    shippingCost = json['shipping_cost']?.toString();
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    statusId = json['status_id'];
    if (json['items'] != null) {
      items = <Items>[];
      json['items'].forEach((v) {
        items!.add(Items.fromJson(v));
      });
    }
    address =
    json['address'] != null ? Address.fromJson(json['address']) : null;
  }

}

class Items {
  int? id;
  int? orderId;
  int? productId;
  int? productVariantId;
  int? vendorId;
  int? quantity;
  String? unitPrice;
  String? subtotal;
  String? status;
  String? createdAt;
  String? updatedAt;
  Product? product;
  ProductVariant? productVariant;

  Items(
      {this.id,
        this.orderId,
        this.productId,
        this.productVariantId,
        this.vendorId,
        this.quantity,
        this.unitPrice,
        this.subtotal,
        this.status,
        this.createdAt,
        this.updatedAt,
        this.product,
        this.productVariant});

  Items.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    orderId = json['order_id'];
    productId = json['product_id'];
    productVariantId = json['product_variant_id'];
    vendorId = json['vendor_id'];
    quantity = json['quantity'];
    unitPrice = json['unit_price']?.toString();
    subtotal = json['subtotal']?.toString();
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    product =
    json['product'] != null ? Product.fromJson(json['product']) : null;
    productVariant = json['product_variant'] != null
        ? ProductVariant.fromJson(json['product_variant'])
        : null;
  }


}

class Product {
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
  int? averageRating;
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
        this.averageRating,
        this.images});

  Product.fromJson(Map<String, dynamic> json) {
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
    averageRating = int.tryParse(json['average_rating'].toString());
    if (json['images'] != null) {
      images = <Images>[];
      json['images'].forEach((v) {
        images!.add(Images.fromJson(v));
      });
    }
  }

}

class Images {
  int? id;
  int? productId;
  String? image;
  String? url;

  Images({this.id, this.productId, this.image, this.url});

  Images.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    productId = json['product_id'];
    image = json['image'];
    url = json['url'];
  }

}

class ProductVariant {
  int? id;
  int? productId;
  String? price;
  int? stock;
  String? expiryDate;
  int? expiryDays;
  String? sku;
  bool? isActive;
  bool? isOutOfStock;
  String? createdAt;
  String? updatedAt;
  int? colorTagId;
  String? cost;
  List<VariantValues>? variantValues;

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
        this.cost,
        this.variantValues});

  ProductVariant.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    productId = json['product_id'];
    price = json['price']?.toString();
    stock = json['stock'];
    expiryDate = json['expiry_date'];
    expiryDays = json['expiry_days'];
    sku = json['sku'];
    isActive = json['is_active'];
    isOutOfStock = json['is_out_of_stock'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    colorTagId = json['color_tag_id'];
    cost = json['cost']?.toString();
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
  VariantAttribute? variantAttribute;

  VariantValues(
      {this.id,
        this.productVariantsId,
        this.variantAttributeId,
        this.value,
        this.createdAt,
        this.updatedAt,
        this.variantAttribute});

  VariantValues.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    productVariantsId = json['product_variants_id'];
    variantAttributeId = json['variant_attribute_id'];
    value = json['value'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    variantAttribute = json['variant_attribute'] != null
        ? VariantAttribute.fromJson(json['variant_attribute'])
        : null;
  }


}

class VariantAttribute {
  int? id;
  String? nameArabic;
  String? nameGerman;
  String? createdAt;
  String? updatedAt;
  String? type;
  String? typeValues;

  VariantAttribute(
      {this.id,
        this.nameArabic,
        this.nameGerman,
        this.createdAt,
        this.updatedAt,
        this.type,
        this.typeValues});

  VariantAttribute.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    nameArabic = json['name_arabic'];
    nameGerman = json['name_german'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    type = json['type'];
    typeValues = json['type_values'];
  }


}

class Address {
  int? id;
  int? userId;
  int? countryId;
  int? cityId;
  String? address;
  String? postalCode;
  String? createdAt;
  String? updatedAt;
  String? latitude;
  String? longitude;

  Address(
      {this.id,
        this.userId,
        this.countryId,
        this.cityId,
        this.address,
        this.postalCode,
        this.createdAt,
        this.updatedAt,
        this.latitude,
        this.longitude});

  Address.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    userId = json['user_id'];
    countryId = json['country_id'];
    cityId = json['city_id'];
    address = json['address'];
    postalCode = json['postal_code'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    latitude = json['latitude'];
    longitude = json['longitude'];
  }
}