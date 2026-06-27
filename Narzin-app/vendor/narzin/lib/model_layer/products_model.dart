
class ProductsModel {
  bool? status;
  ProductData? data;
  String? message;

  ProductsModel({this.status, this.data});

  ProductsModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    data = json['data'] != null ? ProductData.fromJson(json['data']) : null;
    message = json['message'];
  }

}

class Data {
  int? currentPage;
  List<ProductData>? data;
  String? firstPageUrl;
  int? from;
  int? lastPage;
  String? lastPageUrl;
  List<Links>? links;
  String? nextPageUrl;
  String? path;
  int? perPage;
  String? prevPageUrl;
  int? to;
  int? total;

  Data(
      {this.currentPage,
        this.data,
        this.firstPageUrl,
        this.from,
        this.lastPage,
        this.lastPageUrl,
        this.links,
        this.nextPageUrl,
        this.path,
        this.perPage,
        this.prevPageUrl,
        this.to,
        this.total});

  Data.fromJson(Map<String, dynamic> json) {
    currentPage = json['current_page'];
    if (json['data'] != null) {
      data = <ProductData>[];
      json['data'].forEach((v) {
        data!.add(ProductData.fromJson(v));
      });
    }
    firstPageUrl = json['first_page_url'];
    from = json['from'];
    lastPage = json['last_page'];
    lastPageUrl = json['last_page_url'];
    if (json['links'] != null) {
      links = <Links>[];
      json['links'].forEach((v) {
        links!.add(Links.fromJson(v));
      });
    }
    nextPageUrl = json['next_page_url'];
    path = json['path'];
    perPage = json['per_page'];
    prevPageUrl = json['prev_page_url'];
    to = json['to'];
    total = json['total'];
  }
}

class ProductData {
  int? id;
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? descriptionArabic;
  String? descriptionGerman;
  int? categoryId;
  bool? isActive;
  int? vendorId;
  String? createdAt;
  String? updatedAt;
  String? minPrice;
  int? minPriceVariantId;
  List<String>? images;
  Category? category;

  ProductData(
      {this.id,
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
        this.minPrice,
        this.minPriceVariantId,
        this.images,
        this.category});

  ProductData.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    nameArabic = json['name_arabic'];
    nameGerman = json['name_german'];
    slugArabic = json['slug_arabic'];
    slugGerman = json['slug_german'];
    descriptionArabic = json['description_arabic'];
    descriptionGerman = json['description_german'];
    categoryId = json['category_id'];
    isActive = json['is_active'];
    vendorId = json['vendor_id'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    minPrice = json['min_price'];
    minPriceVariantId = json['min_price_variant_id'];
    if (json['images'] != null) {
      images = <String>[];
      json['images'].forEach((v) {
        images!.add(v);
      });
    }
    category = json['category'] != null
        ? Category.fromJson(json['category'])
        : null;
  }
}

class Category {
  int? id;
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? image;
  int? parentId;
  String? createdAt;
  String? updatedAt;

  Category(
      {this.id,
        this.nameArabic,
        this.nameGerman,
        this.slugArabic,
        this.slugGerman,
        this.image,
        this.parentId,
        this.createdAt,
        this.updatedAt});

  Category.fromJson(Map<String, dynamic> json) {
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
}

class Links {
  String? url;
  String? label;
  bool? active;

  Links({this.url, this.label, this.active});

  Links.fromJson(Map<String, dynamic> json) {
    url = json['url'];
    label = json['label'];
    active = json['active'];
  }
}