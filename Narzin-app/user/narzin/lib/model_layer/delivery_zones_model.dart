class DeliveryZonesModel {
  bool? status;
  String? message;
  List<DeliveryZone>? data;

  DeliveryZonesModel({this.status, this.message, this.data});

  DeliveryZonesModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message'];
    if (json['data'] != null) {
      data = <DeliveryZone>[];
      json['data'].forEach((v) {
        data!.add(DeliveryZone.fromJson(v));
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

class DeliveryZone {
  int? id;
  String? name;
  String? status;
  List<DeliveryMethod>? deliveryMethods;

  DeliveryZone({this.id, this.name, this.status, this.deliveryMethods});

  DeliveryZone.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    status = json['status'];
    if (json['delivery_methods'] != null) {
      deliveryMethods = <DeliveryMethod>[];
      json['delivery_methods'].forEach((v) {
        deliveryMethods!.add(DeliveryMethod.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['status'] = status;
    if (deliveryMethods != null) {
      data['delivery_methods'] =
          deliveryMethods!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class DeliveryMethod {
  int? id;
  int? deliveryZoneId;
  String? name;
  String? estimatedDays;
  String? basePrice;
  String? pricePerKg;

  DeliveryMethod(
      {this.id,
      this.deliveryZoneId,
      this.name,
      this.estimatedDays,
      this.basePrice,
      this.pricePerKg});

  DeliveryMethod.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    deliveryZoneId = json['delivery_zone_id'];
    name = json['name'];
    estimatedDays = json['estimated_days'];
    basePrice = json['base_price']?.toString();
    pricePerKg = json['price_per_kg']?.toString();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['delivery_zone_id'] = deliveryZoneId;
    data['name'] = name;
    data['estimated_days'] = estimatedDays;
    data['base_price'] = basePrice;
    data['price_per_kg'] = pricePerKg;
    return data;
  }
}
