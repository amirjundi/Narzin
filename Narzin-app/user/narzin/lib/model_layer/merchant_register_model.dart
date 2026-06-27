class MerchantRegisterModel {
  bool? status;
  String? message;
  Data? data;

  MerchantRegisterModel({this.status, this.message, this.data});

  MerchantRegisterModel.fromJson(Map<String, dynamic> json) {
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
  String? storeNameInArabic;
  String? storeNameInGerman;
  String? address;
  String? phone;
  String? storeType;
  String? latitude;
  String? longitude;
  int? userId;
  String? storeLogo;
  String? storeId;
  String? updatedAt;
  String? createdAt;
  int? id;

  Data(
      {this.storeNameInArabic,
        this.storeNameInGerman,
        this.address,
        this.phone,
        this.storeType,
        this.latitude,
        this.longitude,
        this.userId,
        this.storeLogo,
        this.storeId,
        this.updatedAt,
        this.createdAt,
        this.id});

  Data.fromJson(Map<String, dynamic> json) {
    storeNameInArabic = json['store_name_in_arabic'];
    storeNameInGerman = json['store_name_in_german'];
    address = json['address'];
    phone = json['phone'];
    storeType = json['store_type'];
    latitude = json['latitude'];
    longitude = json['longitude'];
    userId = json['user_id'];
    storeLogo = json['store_logo'];
    storeId = json['store_id'];
    updatedAt = json['updated_at'];
    createdAt = json['created_at'];
    id = json['id'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['store_name_in_arabic'] = storeNameInArabic;
    data['store_name_in_german'] = storeNameInGerman;
    data['address'] = address;
    data['phone'] = phone;
    data['store_type'] = storeType;
    data['latitude'] = latitude;
    data['longitude'] = longitude;
    data['user_id'] = userId;
    data['store_logo'] = storeLogo;
    data['store_id'] = storeId;
    data['updated_at'] = updatedAt;
    data['created_at'] = createdAt;
    data['id'] = id;
    return data;
  }
}