class ShippingPriceModel {
  bool? status;
  String? message;
  Data? data;

  ShippingPriceModel({this.status, this.message, this.data});

  ShippingPriceModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message']?.toString();
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
  String? id;
  String? price;
  String? fastPrice;
  String? fromDays;
  String? toDays;
  String? createdAt;
  String? updatedAt;

  Data({
    this.id,
    this.price,
    this.fastPrice,
    this.fromDays,
    this.toDays,
    this.createdAt,
    this.updatedAt,
  });

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    price = json['price']?.toString();
    fastPrice = json['fast_price']?.toString();
    fromDays = json['from_days']?.toString();
    toDays = json['to_days']?.toString();
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['price'] = price;
    data['fast_price'] = fastPrice;
    data['from_days'] = fromDays;
    data['to_days'] = toDays;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}