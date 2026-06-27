class CategoriesModel {
  bool? status;
  List<CategoryData>? data;

  CategoriesModel({this.status, this.data});

  CategoriesModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    if (json['data'] != null) {
      data = <CategoryData>[];
      json['data'].forEach((v) {
        data!.add(CategoryData.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['status'] = status;
    if (this.data != null) {
      data['data'] = this.data!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class CategoryData {
  String? id; // Changed from int? to String?
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? image;
  String? parentId; // Changed from int? to String?
  String? createdAt;
  String? updatedAt;
  List<SubCategories>? subCategories;

  CategoryData({
    this.id,
    this.nameArabic,
    this.nameGerman,
    this.slugArabic,
    this.slugGerman,
    this.image,
    this.parentId,
    this.createdAt,
    this.updatedAt,
    this.subCategories,
  });

  CategoryData.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString(); // Convert to string
    nameArabic = json['name_arabic']?.toString(); // Convert to string
    nameGerman = json['name_german']?.toString(); // Convert to string
    slugArabic = json['slug_arabic']?.toString(); // Convert to string
    slugGerman = json['slug_german']?.toString(); // Convert to string
    image = json['image']?.toString(); // Convert to string
    parentId = json['parent_id']?.toString(); // Convert to string
    createdAt = json['created_at']?.toString(); // Convert to string
    updatedAt = json['updated_at']?.toString(); // Convert to string
    if (json['sub_categories'] != null) {
      subCategories = <SubCategories>[];
      json['sub_categories'].forEach((v) {
        subCategories!.add(SubCategories.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name_arabic'] = nameArabic;
    data['name_german'] = nameGerman;
    data['slug_arabic'] = slugArabic;
    data['slug_german'] = slugGerman;
    data['image'] = image;
    data['parent_id'] = parentId;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    if (subCategories != null) {
      data['sub_categories'] = subCategories!.map((v) => v?.toJson()).toList();
    }
    return data;
  }
}

class SubCategories {
  String? id; // Changed from int? to String?
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? image;
  String? parentId; // Changed from int? to String?
  String? createdAt;
  String? updatedAt;

  SubCategories({
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

  SubCategories.fromJson(Map<String, dynamic> json) {
    id = json['id']?.toString(); // Convert to string
    nameArabic = json['name_arabic']?.toString(); // Convert to string
    nameGerman = json['name_german']?.toString(); // Convert to string
    slugArabic = json['slug_arabic']?.toString(); // Convert to string
    slugGerman = json['slug_german']?.toString(); // Convert to string
    image = json['image']?.toString(); // Convert to string
    parentId = json['parent_id']?.toString(); // Convert to string
    createdAt = json['created_at']?.toString(); // Convert to string
    updatedAt = json['updated_at']?.toString(); // Convert to string
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name_arabic'] = nameArabic;
    data['name_german'] = nameGerman;
    data['slug_arabic'] = slugArabic;
    data['slug_german'] = slugGerman;
    data['image'] = image;
    data['parent_id'] = parentId;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}