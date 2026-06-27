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
  int? id;
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? image;
  int? parentId;
  String? createdAt;
  String? updatedAt;

  CategoryData(
      {this.id,
        this.nameArabic,
        this.nameGerman,
        this.slugArabic,
        this.slugGerman,
        this.image,
        this.parentId,
        this.createdAt,
        this.updatedAt});

  CategoryData.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    nameArabic = json['name_arabic'];
    nameGerman = json['name_german'];
    slugArabic = json['slug_arabic'];
    slugGerman = json['slug_german'];
    image = json['image'];
    parentId = json['parent_id'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
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