class VendorLoginModel {
  bool? status;
  String? message;
  Data? data;

  VendorLoginModel({this.status, this.message, this.data});

  VendorLoginModel.fromJson(Map<String, dynamic> json) {
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
  User? user;
  VendorDetails? vendorDetails;
  String? token;
  String? tokenType;

  Data({this.user, this.vendorDetails, this.token, this.tokenType});

  Data.fromJson(Map<String, dynamic> json) {
    user = json['user'] != null ? User.fromJson(json['user']) : null;
    vendorDetails = json['vendor_details'] != null
        ? VendorDetails.fromJson(json['vendor_details'])
        : null;
    token = json['token'];
    tokenType = json['token_type'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    if (user != null) {
      data['user'] = user!.toJson();
    }
    if (vendorDetails != null) {
      data['vendor_details'] = vendorDetails!.toJson();
    }
    data['token'] = token;
    data['token_type'] = tokenType;
    return data;
  }
}

class User {
  int? id;
  String? name;
  String? email;
  String? createdAt;
  String? emailVerifiedAt;

  User({this.id, this.name, this.email, this.createdAt, this.emailVerifiedAt});

  User.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    email = json['email'];
    createdAt = json['created_at'];
    emailVerifiedAt = json['email_verified_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['email'] = email;
    data['created_at'] = createdAt;
    data['email_verified_at'] = emailVerifiedAt;
    return data;
  }
}

class VendorDetails {
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

  VendorDetails(
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

  VendorDetails.fromJson(Map<String, dynamic> json) {
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

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['store_logo'] = storeLogo;
    data['address'] = address;
    data['phone'] = phone;
    data['store_type'] = storeType;
    data['store_id'] = storeId;
    data['user_id'] = userId;
    data['status'] = status;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['store_name_in_arabic'] = storeNameInArabic;
    data['store_name_in_german'] = storeNameInGerman;
    data['latitude'] = latitude;
    data['longitude'] = longitude;
    return data;
  }
}