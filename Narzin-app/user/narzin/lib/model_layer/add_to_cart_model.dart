class AddToCartResponseModel {
  bool? status;
  String? message;
  Data? data;

  AddToCartResponseModel({this.status, this.message, this.data});

  AddToCartResponseModel.fromJson(Map<String, dynamic> json) {
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
  int? userId;
  String? productId;
  String? productVariantId;
  String? quantity;
  String? updatedAt;
  String? createdAt;
  int? id;

  Data(
      {this.userId,
        this.productId,
        this.productVariantId,
        this.quantity,
        this.updatedAt,
        this.createdAt,
        this.id});

  Data.fromJson(Map<String, dynamic> json) {
    userId = json['user_id'];
    productId = json['product_id'];
    productVariantId = json['product_variant_id'];
    quantity = json['quantity'];
    updatedAt = json['updated_at'];
    createdAt = json['created_at'];
    id = json['id'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['user_id'] = userId;
    data['product_id'] = productId;
    data['product_variant_id'] = productVariantId;
    data['quantity'] = quantity;
    data['updated_at'] = updatedAt;
    data['created_at'] = createdAt;
    data['id'] = id;
    return data;
  }
}