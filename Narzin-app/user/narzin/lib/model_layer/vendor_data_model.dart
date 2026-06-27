class VendorDataModel {
  bool? status;
  String? message;
  Data? data;

  VendorDataModel({this.status, this.message, this.data});

  VendorDataModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
  }
}

class Data {
  int? id;
  String? storeLogo;
  String? address;
  String? phone;
  String? storeType;
  String? storeId;
  int? userId;
  String? status;
  String? createdAt;
  String? updatedAt;
  String? storeNameInArabic;
  String? storeNameInGerman;
  String? latitude;
  String? longitude;

  Data(
      {this.id,
        this.storeLogo,
        this.address,
        this.phone,
        this.storeType,
        this.storeId,
        this.userId,
        this.status,
        this.createdAt,
        this.updatedAt,
        this.storeNameInArabic,
        this.storeNameInGerman,
        this.latitude,
        this.longitude});

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    storeLogo = json['store_logo'];
    address = json['address'];
    phone = json['phone'];
    storeType = json['store_type'];
    storeId = json['store_id'];
    userId = json['user_id'];
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    storeNameInArabic = json['store_name_in_arabic'];
    storeNameInGerman = json['store_name_in_german'];
    latitude = json['latitude'];
    longitude = json['longitude'];
  }
}