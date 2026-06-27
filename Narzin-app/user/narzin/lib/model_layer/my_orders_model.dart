class MyOrdersModel {
  bool? status;
  String? message;
  Data? data;

  MyOrdersModel({this.status, this.data, this.message});

  MyOrdersModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['status'] = status;
    data['message'] = message;
    if (this.data != null) {
      data['data'] = this.data!.toJson();
    }
    return data;
  }
}

class Data {
  String? currentPage;
  List<MyOrder>? data;
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

  Data({
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

  Data.fromJson(Map<String, dynamic> json) {
    currentPage = json['current_page']?.toString();
    if (json['data'] != null) {
      data = <MyOrder>[];
      json['data'].forEach((v) {
        data!.add(MyOrder.fromJson(v));
      });
    }
    firstPageUrl = json['first_page_url'];
    from = json['from']?.toString();
    lastPage = json['last_page']?.toString();
    lastPageUrl = json['last_page_url'];
    if (json['links'] != null) {
      links = <Links>[];
      json['links'].forEach((v) {
        links!.add(Links.fromJson(v));
      });
    }
    nextPageUrl = json['next_page_url'];
    path = json['path'];
    perPage = json['per_page']?.toString();
    prevPageUrl = json['prev_page_url'];
    to = json['to']?.toString();
    total = json['total']?.toString();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['current_page'] = currentPage;
    if (this.data != null) {
      data['data'] = this.data!.map((v) => v.toJson()).toList();
    }
    data['first_page_url'] = firstPageUrl;
    data['from'] = from;
    data['last_page'] = lastPage;
    data['last_page_url'] = lastPageUrl;
    if (links != null) {
      data['links'] = links!.map((v) => v.toJson()).toList();
    }
    data['next_page_url'] = nextPageUrl;
    data['path'] = path;
    data['per_page'] = perPage;
    data['prev_page_url'] = prevPageUrl;
    data['to'] = to;
    data['total'] = total;
    return data;
  }
}

class MyOrder {
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
  List<Items>? items;
  Address? address;

  MyOrder({
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
    this.address,
  });

  MyOrder.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    userId = json['user_id']?.toString();
    addressId = json['address_id']?.toString();
    orderNumber = json['order_number'];
    totalAmount = json['total_amount']?.toString();
    priceAfterDiscount = json['price_after_discount']?.toString();
    walletUsage = json['wallet_usage']?.toString();
    finalPrice = json['final_price']?.toString();
    paymentStatus = json['payment_status'];
    orderStatus = json['order_status'];
    notes = json['notes'];
    couponId = json['coupon_id']?.toString();
    shippingType = json['shipping_type'];
    shippingCost = json['shipping_cost']?.toString();
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    statusId = json['status_id']?.toString();
    if (json['items'] != null) {
      items = <Items>[];
      json['items'].forEach((v) {
        items!.add(Items.fromJson(v));
      });
    }
    address = json['address'] != null ? Address.fromJson(json['address']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = {};
    data['id'] = id;
    data['user_id'] = userId;
    data['address_id'] = addressId;
    data['order_number'] = orderNumber;
    data['total_amount'] = totalAmount;
    data['price_after_discount'] = priceAfterDiscount;
    data['wallet_usage'] = walletUsage;
    data['final_price'] = finalPrice;
    data['payment_status'] = paymentStatus;
    data['order_status'] = orderStatus;
    data['notes'] = notes;
    data['coupon_id'] = couponId;
    data['shipping_type'] = shippingType;
    data['shipping_cost'] = shippingCost;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['status_id'] = statusId;
    if (items != null) {
      data['items'] = items!.map((v) => v.toJson()).toList();
    }
    if (address != null) {
      data['address'] = address!.toJson();
    }
    return data;
  }
}

class Items {
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
    id = json['id']?.toString();
    orderId = json['order_id']?.toString();
    productId = json['product_id']?.toString();
    productVariantId = json['product_variant_id']?.toString();
    vendorId = json['vendor_id']?.toString();
    quantity = json['quantity']?.toString();
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

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['order_id'] = orderId;
    data['product_id'] = productId;
    data['product_variant_id'] = productVariantId;
    data['vendor_id'] = vendorId;
    data['quantity'] = quantity;
    data['unit_price'] = unitPrice;
    data['subtotal'] = subtotal;
    data['status'] = status;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
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
  Vendor? vendor;
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
        this.vendor,
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
    vendor =
    json['vendor'] != null ? Vendor.fromJson(json['vendor']) : null;
    if (json['images'] != null) {
      images = <Images>[];
      json['images'].forEach((v) {
        images!.add(Images.fromJson(v));
      });
    }
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
    if (vendor != null) {
      data['vendor'] = vendor!.toJson();
    }
    if (images != null) {
      data['images'] = images!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Vendor {
  String? id;
  String? storeNameInArabic;
  String? storeNameInGerman;
  String? storeLogo;
  String? address;
  String? phone;
  String? storeType;
  String? storeId;
  String? latitude;
  String? longitude;
  String? userId;
  String? status;
  String? createdAt;
  String? updatedAt;

  Vendor(
      {this.id,
        this.storeNameInArabic,
        this.storeNameInGerman,
        this.storeLogo,
        this.address,
        this.phone,
        this.storeType,
        this.storeId,
        this.latitude,
        this.longitude,
        this.userId,
        this.status,
        this.createdAt,
        this.updatedAt});

  Vendor.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    storeNameInArabic = json['store_name_in_arabic'];
    storeNameInGerman = json['store_name_in_german'];
    storeLogo = json['store_logo'];
    address = json['address'];
    phone = json['phone'];
    storeType = json['store_type'];
    storeId = json['store_id'];
    latitude = json['latitude']?.toString();
    longitude = json['longitude']?.toString();
    userId = json['user_id']?.toString();
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['store_name_in_arabic'] = storeNameInArabic;
    data['store_name_in_german'] = storeNameInGerman;
    data['store_logo'] = storeLogo;
    data['address'] = address;
    data['phone'] = phone;
    data['store_type'] = storeType;
    data['store_id'] = storeId;
    data['latitude'] = latitude;
    data['longitude'] = longitude;
    data['user_id'] = userId;
    data['status'] = status;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
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
    final Map<String, dynamic> data = <String, dynamic>{};
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
    if (json['variant_values'] != null) {
      variantValues = <VariantValues>[];
      json['variant_values'].forEach((v) {
        variantValues!.add(VariantValues.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
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
    if (variantValues != null) {
      data['variant_values'] =
          variantValues!.map((v) => v.toJson()).toList();
    }
    return data;
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

  VariantValues(
      {this.id,
        this.productVariantsId,
        this.variantAttributeId,
        this.value,
        this.createdAt,
        this.updatedAt,
        this.variantAttribute});

  VariantValues.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    productVariantsId = json['product_variants_id']?.toString();
    variantAttributeId = json['variant_attribute_id']?.toString();
    value = json['value'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
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

  VariantAttribute(
      {this.id,
        this.nameArabic,
        this.nameGerman,
        this.createdAt,
        this.updatedAt,
        this.type,
        this.typeValues});

  VariantAttribute.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    nameArabic = json['name_arabic'];
    nameGerman = json['name_german'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    type = json['type'];
    typeValues = json['type_values'];
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

  Address(
      {this.id,
        this.userId,
        this.countryId,
        this.cityId,
        this.address,
        this.phoneNumber,
        this.postalCode,
        this.createdAt,
        this.updatedAt,
        this.latitude,
        this.longitude});

  Address.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    userId = json['user_id']?.toString();
    countryId = json['country_id']?.toString();
    cityId = json['city_id']?.toString();
    address = json['address'];
    phoneNumber = json['phone_number'];
    postalCode = json['postal_code']?.toString();
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
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
    url = json['url'];
    label = json['label'];
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