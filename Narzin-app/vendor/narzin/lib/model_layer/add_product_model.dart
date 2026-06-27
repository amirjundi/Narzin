class AddingProductModel {
  String? nameArabic;
  String? nameGerman;
  String? descriptionArabic;
  String? descriptionGerman;
  int? categoryId;
  int? child_category_id;
  String? weight;
  List<String>? images;
  List<VariantsToPost>? variants;

  AddingProductModel(
      {this.nameArabic,
        this.nameGerman,
        this.descriptionArabic,
        this.descriptionGerman,
        this.categoryId,
        this.images,
        this.weight,
        this.child_category_id,
        this.variants});

  AddingProductModel.fromJson(Map<String, dynamic> json) {
    nameArabic = json['name_arabic'];
    nameGerman = json['name_german'];
    descriptionArabic = json['description_arabic'];
    descriptionGerman = json['description_german'];
    categoryId = json['category_id'];
    child_category_id = json['child_category_id'];
    weight = json['weight'];
    images = json['images[]'].cast<String>();
    if (json['variants'] != null) {
      variants = <VariantsToPost>[];
      json['variants'].forEach((v) {
        variants!.add(VariantsToPost.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['name_arabic'] = nameArabic;
    data['name_german'] = nameGerman;
    data['description_arabic'] = descriptionArabic;
    data['description_german'] = descriptionGerman;
    data['category_id'] = categoryId;
    data['child_category_id'] = child_category_id;
    data['weight'] = weight;
    data['images'] = images; // Ensure 'images[]' is used if backend expects it
    if (variants != null) {
      data['variants'] = variants!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class VariantsToPost {
  int? id;
  double? price;
  int? stock;
  double? cost;
  double? tax;
  int? expiryDays;
  int? color_tag_id;
  List<AttributesToPost>? attributes;

  VariantsToPost({this.id,this.price, this.stock,this.cost,this.tax, this.expiryDays, this.attributes,this.color_tag_id});

  VariantsToPost.fromJson(Map<String, dynamic> json) {
    price = json['price'];
    stock = json['stock'];
    cost = json['cost'];
    tax = json['tax'];
    expiryDays = json['expiry_days'];
    if (json['attributes'] != null) {
      attributes = <AttributesToPost>[];
      json['attributes'].forEach((v) {
        attributes!.add(AttributesToPost.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    if(id != null){
      data['id'] = id;
    }
    data['price'] = price;
    data['color_tag_id'] = color_tag_id;
    data['stock'] = stock;
    data['cost'] = cost;
    data['tax'] = tax;
    data['expiry_days'] = expiryDays??0;
    if (attributes != null) {
      data['attributes'] = attributes!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class AttributesToPost {
  int? attributeId;
  String? value;

  AttributesToPost({this.attributeId, this.value});

  AttributesToPost.fromJson(Map<String, dynamic> json) {
    attributeId = json['attribute_id'];
    value = json['value'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['attribute_id'] = attributeId;
    data['value'] = value;
    return data;
  }
}