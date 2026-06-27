class AddToCartBodyModel {
  String? productId;
  String? productVariantId;
  String? quantity;

  AddToCartBodyModel({this.productId, this.productVariantId, this.quantity});


  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['product_id'] = productId;
    data['product_variant_id'] = productVariantId;
    data['quantity'] = quantity;
    return data;
  }
}