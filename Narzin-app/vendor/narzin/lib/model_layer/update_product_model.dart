import 'package:narzin/model_layer/add_product_model.dart';

class UpdateProductModel {
  String? nameArabic;
  String? nameGerman;
  String? descriptionArabic;
  String? descriptionGerman;
  int? categoryId;
  int? child_category_id;
  bool? isActive;
  String? weight;
  List<VariantsToPost>? variants;
  List<VariantsToPost>? newVariants;
  List<int>? deleteVariants;

  UpdateProductModel(
      {this.nameArabic,
        this.nameGerman,
        this.descriptionArabic,
        this.descriptionGerman,
        this.categoryId,
        this.child_category_id,
        this.isActive,
        this.weight,
        this.variants,
        this.newVariants,
        this.deleteVariants});

  UpdateProductModel.fromJson(Map<String, dynamic> json) {
    nameArabic = json['name_arabic'];
    nameGerman = json['name_german'];
    descriptionArabic = json['description_arabic'];
    descriptionGerman = json['description_german'];
    categoryId = json['category_id'];
    child_category_id = json['child_category_id'];
    isActive = json['is_active'];
    weight = json['weight'];
    if (json['variants'] != null) {
      variants = <VariantsToPost>[];
      json['variants'].forEach((v) {
        variants!.add(VariantsToPost.fromJson(v));
      });
    }
    if (json['new_variants'] != null) {
      newVariants = <VariantsToPost>[];
      json['new_variants'].forEach((v) {
        newVariants!.add(VariantsToPost.fromJson(v));
      });
    }
    deleteVariants = json['delete_variants'].cast<int>();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['name_arabic'] = nameArabic;
    data['name_german'] = nameGerman;
    data['description_arabic'] = descriptionArabic;
    data['description_german'] = descriptionGerman;
    data['category_id'] = categoryId;
    data['child_category_id'] = child_category_id;
    data['is_active'] = isActive;
    data['weight'] = weight;
    if (variants != null) {
      data['variants'] = variants!.map((v) => v.toJson()).toList();
    }
    if (newVariants != null) {
      data['new_variants'] = newVariants!.map((v) => v.toJson()).toList();
    }
    data['delete_variants'] = deleteVariants;
    return data;
  }
}


