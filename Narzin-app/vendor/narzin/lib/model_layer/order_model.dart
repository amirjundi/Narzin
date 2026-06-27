class OrdersResponse {
  bool? status;
  OrderResponseData? data;
  String? message;

  OrdersResponse({this.status, this.data, this.message});

  OrdersResponse.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message']?.toString();
    data = json['data'] != null ? OrderResponseData.fromJson(json['data']) : null;
  }
}

class OrderResponseData {
  Orders? orders;
  String? totalRevenue;

  OrderResponseData({this.orders, this.totalRevenue});

  OrderResponseData.fromJson(Map<String, dynamic> json) {
    orders = json['orders'] != null ? Orders.fromJson(json['orders']) : null;
    totalRevenue = json['total_revenue']?.toString();
  }
}

class Orders {
  String? currentPage;
  List<OrdersData>? data;
  String? firstPageUrl;
  String? from;
  String? lastPage;
  String? lastPageUrl;
  List<Links>? links;
  String? nextPageUrl;
  String? path;
  String? perPage;
  String? prevPageUrl;
  String? to;
  String? total;

  Orders({
    this.currentPage,
    this.data,
    this.firstPageUrl,
    this.from,
    this.lastPage,
    this.lastPageUrl,
    this.links,
    this.nextPageUrl,
    this.path,
    this.perPage,
    this.prevPageUrl,
    this.to,
    this.total,
  });

  Orders.fromJson(Map<String, dynamic> json) {
    currentPage = json['current_page']?.toString();
    if (json['data'] != null) {
      data = <OrdersData>[];
      json['data'].forEach((v) {
        data!.add(OrdersData.fromJson(v));
      });
    }
    firstPageUrl = json['first_page_url']?.toString();
    from = json['from']?.toString();
    lastPage = json['last_page']?.toString();
    lastPageUrl = json['last_page_url']?.toString();
    if (json['links'] != null) {
      links = <Links>[];
      json['links'].forEach((v) {
        links!.add(Links.fromJson(v));
      });
    }
    nextPageUrl = json['next_page_url']?.toString();
    path = json['path']?.toString();
    perPage = json['per_page']?.toString();
    prevPageUrl = json['prev_page_url']?.toString();
    to = json['to']?.toString();
    total = json['total']?.toString();
  }
}

class OrdersData {
  String? id;
  String? userId;
  String? addressId;
  String? orderNumber;
  String? totalAmount;
  String? priceAfterDiscount;
  String? walletUsage;
  String? finalPrice;
  String? paymentStatus;
  String? orderStatus;
  String? notes;
  String? couponId;
  String? shippingType;
  String? shippingCost;
  String? createdAt;
  String? updatedAt;
  String? statusId;
  List<OrderItemData>? items;
  User? user;
  Address? address;
  String? status;

  OrdersData({
    this.id,
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
    this.user,
    this.address,
    this.status,
  });

  OrdersData.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    userId = json['user_id']?.toString();
    addressId = json['address_id']?.toString();
    orderNumber = json['order_number']?.toString();
    totalAmount = json['total_amount']?.toString();
    priceAfterDiscount = json['price_after_discount']?.toString();
    walletUsage = json['wallet_usage']?.toString();
    finalPrice = json['final_price']?.toString();
    paymentStatus = json['payment_status']?.toString();
    orderStatus = json['order_status']?.toString();
    notes = json['notes']?.toString();
    couponId = json['coupon_id']?.toString();
    shippingType = json['shipping_type']?.toString();
    shippingCost = json['shipping_cost']?.toString();
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
    statusId = json['status_id']?.toString();
    if (json['items'] != null) {
      items = <OrderItemData>[];
      json['items'].forEach((v) {
        items!.add(OrderItemData.fromJson(v));
      });
    }
    user = json['user'] != null ? User.fromJson(json['user']) : null;
    address = json['address'] != null ? Address.fromJson(json['address']) : null;
    status = json['status']?.toString();
  }
}

class OrderItemData {
  String? id;
  String? orderId;
  String? productId;
  String? productVariantId;
  String? vendorId;
  String? quantity;
  String? unitPrice;
  String? subtotal;
  String? status;
  String? createdAt;
  String? updatedAt;
  OrderProduct? product;
  OrderProductVariant? productVariant;

  OrderItemData({
    this.id,
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
    this.productVariant,
  });

  OrderItemData.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    orderId = json['order_id']?.toString();
    productId = json['product_id']?.toString();
    productVariantId = json['product_variant_id']?.toString();
    vendorId = json['vendor_id']?.toString();
    quantity = json['quantity']?.toString();
    unitPrice = json['unit_price']?.toString();
    subtotal = json['subtotal']?.toString();
    status = json['status']?.toString();
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
    product = json['product'] != null ? OrderProduct.fromJson(json['product']) : null;
    productVariant = json['product_variant'] != null
        ? OrderProductVariant.fromJson(json['product_variant'])
        : null;
  }
}

class OrderProduct {
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
  List<Images>? images;

  OrderProduct({
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
    this.images,
  });

  OrderProduct.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    nameArabic = json['name_arabic']?.toString();
    nameGerman = json['name_german']?.toString();
    slugArabic = json['slug_arabic']?.toString();
    slugGerman = json['slug_german']?.toString();
    descriptionArabic = json['description_arabic']?.toString();
    descriptionGerman = json['description_german']?.toString();
    categoryId = json['category_id']?.toString();
    isActive = json['is_active'];
    vendorId = json['vendor_id']?.toString();
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
    childCategoryId = json['child_category_id']?.toString();
    averageRating = json['average_rating']?.toString();
    if (json['images'] != null) {
      images = <Images>[];
      json['images'].forEach((v) {
        images!.add(Images.fromJson(v));
      });
    }
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
    image = json['image']?.toString();
    url = json['url']?.toString();
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

class OrderProductVariant {
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
  List<VariantValues>? variantValues;

  OrderProductVariant({
    this.id,
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
    this.variantValues,
  });

  OrderProductVariant.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    productId = json['product_id']?.toString();
    price = json['price']?.toString();
    stock = json['stock']?.toString();
    expiryDate = json['expiry_date']?.toString();
    expiryDays = json['expiry_days']?.toString();
    sku = json['sku']?.toString();
    isActive = json['is_active'];
    isOutOfStock = json['is_out_of_stock'];
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
    colorTagId = json['color_tag_id']?.toString();
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
  String? id;
  String? productVariantsId;
  String? variantAttributeId;
  String? value;
  String? createdAt;
  String? updatedAt;
  VariantAttribute? variantAttribute;

  VariantValues({
    this.id,
    this.productVariantsId,
    this.variantAttributeId,
    this.value,
    this.createdAt,
    this.updatedAt,
    this.variantAttribute,
  });

  VariantValues.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    productVariantsId = json['product_variants_id']?.toString();
    variantAttributeId = json['variant_attribute_id']?.toString();
    value = json['value']?.toString();
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
    variantAttribute = json['variant_attribute'] != null
        ? VariantAttribute.fromJson(json['variant_attribute'])
        : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['product_variants_id'] = productVariantsId;
    data['variant_attribute_id'] = variantAttributeId;
    data['value'] = value;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    if (variantAttribute != null) {
      data['variant_attribute'] = variantAttribute!.toJson();
    }
    return data;
  }
}

class VariantAttribute {
  String? id;
  String? nameArabic;
  String? nameGerman;
  String? createdAt;
  String? updatedAt;
  String? type;
  String? typeValues;

  VariantAttribute({
    this.id,
    this.nameArabic,
    this.nameGerman,
    this.createdAt,
    this.updatedAt,
    this.type,
    this.typeValues,
  });

  VariantAttribute.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    nameArabic = json['name_arabic']?.toString();
    nameGerman = json['name_german']?.toString();
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
    type = json['type']?.toString();
    typeValues = json['type_values']?.toString();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name_arabic'] = nameArabic;
    data['name_german'] = nameGerman;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['type'] = type;
    data['type_values'] = typeValues;
    return data;
  }
}

class User {
  String? id;
  String? name;
  String? email;
  String? emailVerifiedAt;
  String? createdAt;
  String? updatedAt;
  String? preferredLanguage;
  String? ordersCount;

  User({
    this.id,
    this.name,
    this.email,
    this.emailVerifiedAt,
    this.createdAt,
    this.updatedAt,
    this.preferredLanguage,
    this.ordersCount,
  });

  User.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    name = json['name']?.toString();
    email = json['email']?.toString();
    emailVerifiedAt = json['email_verified_at']?.toString();
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
    preferredLanguage = json['preferred_language']?.toString();
    ordersCount = json['orders_count']?.toString();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['email'] = email;
    data['email_verified_at'] = emailVerifiedAt;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['preferred_language'] = preferredLanguage;
    data['orders_count'] = ordersCount;
    return data;
  }
}

class Address {
  String? id;
  String? userId;
  String? countryId;
  String? cityId;
  String? address;
  String? phoneNumber;
  String? postalCode;
  String? createdAt;
  String? updatedAt;
  String? latitude;
  String? longitude;

  Address({
    this.id,
    this.userId,
    this.countryId,
    this.cityId,
    this.address,
    this.phoneNumber,
    this.postalCode,
    this.createdAt,
    this.updatedAt,
    this.latitude,
    this.longitude,
  });

  Address.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    userId = json['user_id']?.toString();
    countryId = json['country_id']?.toString();
    cityId = json['city_id']?.toString();
    address = json['address']?.toString();
    phoneNumber = json['phone_number']?.toString();
    postalCode = json['postal_code']?.toString();
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
    latitude = json['latitude']?.toString();
    longitude = json['longitude']?.toString();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['user_id'] = userId;
    data['country_id'] = countryId;
    data['city_id'] = cityId;
    data['address'] = address;
    data['phone_number'] = phoneNumber;
    data['postal_code'] = postalCode;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['latitude'] = latitude;
    data['longitude'] = longitude;
    return data;
  }
}

class Links {
  String? url;
  String? label;
  bool? active;

  Links({this.url, this.label, this.active});

  Links.fromJson(Map<String, dynamic> json) {
    url = json['url']?.toString();
    label = json['label']?.toString();
    active = json['active'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['url'] = url;
    data['label'] = label;
    data['active'] = active;
    return data;
  }
}