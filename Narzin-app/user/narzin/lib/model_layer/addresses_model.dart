class AddressesModel {
  bool? status;
  String? message;
  List<AddressData>? data;

  AddressesModel({this.status, this.message, this.data});

  AddressesModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    if (json['data'] != null) {
      data = <AddressData>[];
      json['data'].forEach((v) {
        data!.add(AddressData.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['status'] = status;
    data['message'] = message;
    if (this.data != null) {
      data['data'] = this.data!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class AddressData {
  int? id;
  int? userId;

  String? address;
  String? title;
  String? postalCode;
  String? createdAt;
  String? updatedAt;
  String? latitude;
  String? longitude;

  String? city;
  int? deliveryZoneId;

  AddressData(
      {this.id,
        this.userId,
        this.address,this.title,
        this.postalCode,
        this.createdAt,
        this.updatedAt,
        this.latitude,
        this.longitude,
        this.city});

  AddressData.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    userId = json['user_id'];
    address = json['address'];
    title = json['title'];
    postalCode = json['postal_code'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    latitude = json['latitude'];
    longitude = json['longitude'];
    city = json['city'];
    deliveryZoneId = json['delivery_zone_id'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['user_id'] = userId;

    data['title'] = title;
    data['address'] = address;
    data['postal_code'] = postalCode;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['latitude'] = latitude;
    data['longitude'] = longitude;
    data['city'] = city;

    return data;
  }
}

class Country {
  int? id;
  String? name;
  String? code;
  String? flag;
  String? createdAt;
  String? updatedAt;

  Country(
      {this.id,
        this.name,
        this.code,
        this.flag,
        this.createdAt,
        this.updatedAt});

  Country.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    code = json['code'];
    flag = json['flag'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['code'] = code;
    data['flag'] = flag;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}

class City {
  int? id;
  String? name;
  int? countryId;
  String? price;
  String? fastPrice;
  String? createdAt;
  String? updatedAt;

  City(
      {this.id,
        this.name,
        this.countryId,
        this.price,
        this.fastPrice,
        this.createdAt,
        this.updatedAt});

  City.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    countryId = json['country_id'];
    price = json['price']?.toString();
    fastPrice = json['fast_price']?.toString();
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['country_id'] = countryId;
    data['price'] = price;
    data['fast_price'] = fastPrice;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}