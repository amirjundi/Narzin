class CountriesModel {
  bool? status;
  String? message;
  List<CountryData>? data;

  CountriesModel({this.status, this.message, this.data});

  CountriesModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    if (json['data'] != null) {
      data = <CountryData>[];
      json['data'].forEach((v) {
        data!.add(CountryData.fromJson(v));
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

class CountryData {
  int? id;
  String? name;
  String? code;
  String? flag;
  String? createdAt;
  String? updatedAt;
  List<Cities>? cities;

  CountryData(
      {this.id,
        this.name,
        this.code,
        this.flag,
        this.createdAt,
        this.updatedAt,
        this.cities});

  CountryData.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    code = json['code'];
    flag = json['flag'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    if (json['cities'] != null) {
      cities = <Cities>[];
      json['cities'].forEach((v) {
        cities!.add(Cities.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['code'] = code;
    data['flag'] = flag;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    if (cities != null) {
      data['cities'] = cities!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Cities {
  int? id;
  String? name;
  int? countryId;
  String? price;
  String? fastPrice;
  String? createdAt;
  String? updatedAt;

  Cities(
      {this.id,
        this.name,
        this.countryId,
        this.price,
        this.fastPrice,
        this.createdAt,
        this.updatedAt});

  Cities.fromJson(Map<String, dynamic> json) {
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