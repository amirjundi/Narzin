class SingleProductModel {
  bool? status;
  String? message;
  Data? data;

  SingleProductModel({this.status, this.data, this.message});

  SingleProductModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    message = json['message']?.toString();
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
  }
}

class Data {
  String? id;
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? descriptionArabic;
  String? descriptionGerman;
  String? categoryId;
  bool? isActive;
  String? vendorId;
  String? createdAt;
  String? updatedAt;
  String? childCategoryId;
  String? averageRating;
  List<SingleProductVariants>? variants;
  List<SingleProductImages>? images;
  Category? category;
  SizeChart? sizeChart;

  Data({
    this.id,
    this.nameArabic,
    this.nameGerman,
    this.slugArabic,
    this.slugGerman,
    this.descriptionArabic,
    this.descriptionGerman,
    this.categoryId,
    this.isActive,
    this.vendorId,
    this.createdAt,
    this.updatedAt,
    this.childCategoryId,
    this.averageRating,
    this.variants,
    this.images,
    this.category,
  });

  Data.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    nameArabic = json['name_arabic']?.toString();
    nameGerman = json['name_german']?.toString();
    slugArabic = json['slug_arabic']?.toString();
    slugGerman = json['slug_german']?.toString();
    descriptionArabic = json['description_arabic']?.toString();
    descriptionGerman = json['description_german']?.toString();
    categoryId = json['category_id']?.toString();
    isActive = json['is_active'];
    vendorId = json['vendor_id']?.toString();
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
    childCategoryId = json['child_category_id']?.toString();
    averageRating = json['average_rating']?.toString();
    sizeChart = json['size_chart'] == null ? null : SizeChart.fromJson(Map<String, dynamic>.from(json['size_chart']));

    if (json['variants'] != null) {
      variants = <SingleProductVariants>[];
      json['variants'].forEach((v) {
        variants!.add(SingleProductVariants.fromJson(v));
      });
    }

    if (json['images'] != null) {
      images = <SingleProductImages>[];
      json['images'].forEach((v) {
        images!.add(SingleProductImages.fromJson(v));
      });
    }

    category = json['category'] != null ? Category.fromJson(json['category']) : null;
  }
}

class SingleProductVariants {
  String? id;
  String? price;
  String? stock;
  String? colorTagId;
  String? sku;
  bool? isActive;
  bool? isOutOfStock;
  String? expiryDate;
  String? expiryDays;
  List<Attributes>? attributes;

  SingleProductVariants({
    this.id,
    this.price,
    this.stock,
    this.colorTagId,
    this.sku,
    this.isActive,
    this.isOutOfStock,
    this.expiryDate,
    this.expiryDays,
    this.attributes,
  });

  SingleProductVariants.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    price = json['price']?.toString();
    stock = json['stock']?.toString();
    colorTagId = json['color_tag_id']?.toString();
    sku = json['sku']?.toString();
    isActive = json['is_active'];
    isOutOfStock = json['is_out_of_stock'];
    expiryDate = json['expiry_date']?.toString();
    expiryDays = json['expiry_days']?.toString();

    if (json['attributes'] != null) {
      attributes = <Attributes>[];
      json['attributes'].forEach((v) {
        attributes!.add(Attributes.fromJson(v));
      });
    }
  }
}

class Attributes {
  String? attributeId;
  String? nameArabic;
  String? type;
  String? typeValues;
  String? nameGerman;
  String? value;

  Attributes({
    this.attributeId,
    this.nameArabic,
    this.type,
    this.typeValues,
    this.nameGerman,
    this.value,
  });

  Attributes.fromJson(Map<String, dynamic> json) {
    attributeId = json['attribute_id']?.toString();
    nameArabic = json['name_arabic']?.toString();
    type = json['type']?.toString();
    typeValues = json['type_values']?.toString();
    nameGerman = json['name_german']?.toString();
    value = json['value']?.toString();
  }
}

class SingleProductImages {
  String? id;
  String? productId;
  String? color;
  String? image;
  String? url;

  SingleProductImages({this.id, this.productId, this.color, this.image, this.url});

  SingleProductImages.fromJson(Map<String, dynamic> json) {
    id = json['id'].toString();
    productId = json['product_id'].toString();
    color = json['color'].toString();
    image = json['image'].toString();
    url = json['url'].toString();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['product_id'] = productId;
    data['color'] = color;
    data['image'] = image;
    data['url'] = url;
    return data;
  }
}

class Category {
  String? id;
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? image;
  String? parentId;
  String? createdAt;
  String? updatedAt;

  Category({
    this.id,
    this.nameArabic,
    this.nameGerman,
    this.slugArabic,
    this.slugGerman,
    this.image,
    this.parentId,
    this.createdAt,
    this.updatedAt,
  });

  Category.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString();
    nameArabic = json['name_arabic']?.toString();
    nameGerman = json['name_german']?.toString();
    slugArabic = json['slug_arabic']?.toString();
    slugGerman = json['slug_german']?.toString();
    image = json['image']?.toString();
    parentId = json['parent_id']?.toString();
    createdAt = json['created_at']?.toString();
    updatedAt = json['updated_at']?.toString();
  }
}

class SizeChart {
  final String unit;
  final List<String> columns;
  final List<SizeRow> rows;

  SizeChart({required this.unit, required this.columns, required this.rows});

  factory SizeChart.fromJson(Map<String, dynamic> json) => SizeChart(
        unit: json['unit'] ?? 'cm',
        columns: List<String>.from(json['columns'] ?? const []),
        rows: (json['rows'] as List? ?? const [])
            .map((r) => SizeRow.fromJson(Map<String, dynamic>.from(r)))
            .toList(),
      );
}

class SizeRow {
  final String size;
  final Map<String, dynamic> values;
  SizeRow({required this.size, required this.values});
  factory SizeRow.fromJson(Map<String, dynamic> json) => SizeRow(
        size: json['size'] ?? '',
        values: Map<String, dynamic>.from(json['values'] ?? const {}),
      );
}
