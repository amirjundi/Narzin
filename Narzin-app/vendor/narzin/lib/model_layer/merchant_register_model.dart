class MerchantRegisterModel {
  bool? status;
  String? message;
  Data? data;

  MerchantRegisterModel({this.status, this.message, this.data});

  MerchantRegisterModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    data = json['data'] != null ? new Data.fromJson(json['data']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['status'] = this.status;
    data['message'] = this.message;
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
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['store_name_in_arabic'] = this.storeNameInArabic;
    data['store_name_in_german'] = this.storeNameInGerman;
    data['address'] = this.address;
    data['phone'] = this.phone;
    data['store_type'] = this.storeType;
    data['latitude'] = this.latitude;
    data['longitude'] = this.longitude;
    data['user_id'] = this.userId;
    data['store_logo'] = this.storeLogo;
    data['store_id'] = this.storeId;
    data['updated_at'] = this.updatedAt;
    data['created_at'] = this.createdAt;
    data['id'] = this.id;
    return data;
  }
}