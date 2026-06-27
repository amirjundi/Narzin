class VendorLoginModel {
  bool? status;
  String? message;
  Data? data;

  VendorLoginModel({this.status, this.message, this.data});

  VendorLoginModel.fromJson(Map<String, dynamic> json) {
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
  User? user;
  VendorDetails? vendorDetails;
  String? token;
  String? tokenType;

  Data({this.user, this.vendorDetails, this.token, this.tokenType});

  Data.fromJson(Map<String, dynamic> json) {
    user = json['user'] != null ? new User.fromJson(json['user']) : null;
    vendorDetails = json['vendor_details'] != null
        ? new VendorDetails.fromJson(json['vendor_details'])
        : null;
    token = json['token'];
    tokenType = json['token_type'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = new Map<String, dynamic>();
    if (this.user != null) {
      data['user'] = this.user!.toJson();
    }
    if (this.vendorDetails != null) {
      data['vendor_details'] = this.vendorDetails!.toJson();
    }
    data['token'] = this.token;
    data['token_type'] = this.tokenType;
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
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['name'] = this.name;
    data['email'] = this.email;
    data['created_at'] = this.createdAt;
    data['email_verified_at'] = this.emailVerifiedAt;
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
    final Map<String, dynamic> data = new Map<String, dynamic>();
    data['id'] = this.id;
    data['store_logo'] = this.storeLogo;
    data['address'] = this.address;
    data['phone'] = this.phone;
    data['store_type'] = this.storeType;
    data['store_id'] = this.storeId;
    data['user_id'] = this.userId;
    data['status'] = this.status;
    data['created_at'] = this.createdAt;
    data['updated_at'] = this.updatedAt;
    data['store_name_in_arabic'] = this.storeNameInArabic;
    data['store_name_in_german'] = this.storeNameInGerman;
    data['latitude'] = this.latitude;
    data['longitude'] = this.longitude;
    return data;
  }
}