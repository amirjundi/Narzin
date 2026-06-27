class CouponsModel {
  bool? status;
  String? message;
  Data? data;

  CouponsModel({this.status, this.message, this.data});

  CouponsModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
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
  int? id;
  int? vendorId;
  String? code;
  String? discountAmount;
  String? discountType;
  String? startDate;
  String? endDate;
  int? usageLimit;
  int? used;
  String? minimumCartAmount;
  int? isActive;
  String? createdAt;
  String? updatedAt;
  Vendor? vendor;

  Data(
      {this.id,
        this.vendorId,
        this.code,
        this.discountAmount,
        this.discountType,
        this.startDate,
        this.endDate,
        this.usageLimit,
        this.used,
        this.minimumCartAmount,
        this.isActive,
        this.createdAt,
        this.updatedAt,
        this.vendor});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    vendorId = json['vendor_id'];
    code = json['code'];
    discountAmount = json['discount_amount']?.toString();
    discountType = json['discount_type'];
    startDate = json['start_date'];
    endDate = json['end_date'];
    usageLimit = json['usage_limit'];
    used = json['used'];
    minimumCartAmount = json['minimum_cart_amount']?.toString();
    isActive = json['is_active'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    vendor =
    json['vendor'] != null ? Vendor.fromJson(json['vendor']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['vendor_id'] = vendorId;
    data['code'] = code;
    data['discount_amount'] = discountAmount;
    data['discount_type'] = discountType;
    data['start_date'] = startDate;
    data['end_date'] = endDate;
    data['usage_limit'] = usageLimit;
    data['used'] = used;
    data['minimum_cart_amount'] = minimumCartAmount;
    data['is_active'] = isActive;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    if (vendor != null) {
      data['vendor'] = vendor!.toJson();
    }
    return data;
  }
}

class Vendor {
  int? id;
  String? storeNameInArabic;
  String? storeNameInGerman;
  String? storeLogo;
  String? address;
  String? phone;
  String? storeType;
  String? storeId;
  String? latitude;
  String? longitude;
  int? userId;
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
    id = json['id'];
    storeNameInArabic = json['store_name_in_arabic'];
    storeNameInGerman = json['store_name_in_german'];
    storeLogo = json['store_logo'];
    address = json['address'];
    phone = json['phone'];
    storeType = json['store_type'];
    storeId = json['store_id'];
    latitude = json['latitude'];
    longitude = json['longitude'];
    userId = json['user_id'];
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