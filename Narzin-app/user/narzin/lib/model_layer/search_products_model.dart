String? _asString(dynamic v) => v?.toString();
class SearchProductsModel {
  bool? status;
  Data? data;

  SearchProductsModel({this.status, this.data});

  SearchProductsModel.fromJson(Map<String, dynamic> json) {
    status = json['status'];
    data = json['data'] != null ? Data.fromJson(json['data']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['status'] = status;
    if (this.data != null) {
      data['data'] = this.data!.toJson();
    }
    return data;
  }
}

class Data {
  Products? products;
  Filters? filters;

  Data({this.products, this.filters});

  Data.fromJson(Map<String, dynamic> json) {
    products = json['products'] != null ? Products.fromJson(json['products']) : null;
    filters = json['filters'] != null ? Filters.fromJson(json['filters']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    if (products != null) data['products'] = products!.toJson();
    if (filters != null) data['filters'] = filters!.toJson();
    return data;
  }
}

class Products {
  String? currentPage;
  List<ProductsData>? data;
  String? firstPageUrl;
  String? from;
  String? lastPage;
  String? lastPageUrl;
  List<Links>? links;
  String? nextPageUrl;
  String? path;
  String? perPage;
  String? prevPageUrl;
  String? to;
  String? total;

  Products.fromJson(Map<String, dynamic> json) {
    currentPage = _asString(json['current_page']);
    if (json['data'] != null) {
      data = <ProductsData>[];
      json['data'].forEach((v) {
        data!.add(ProductsData.fromJson(v));
      });
    }
    firstPageUrl = _asString(json['first_page_url']);
    from = _asString(json['from']);
    lastPage = _asString(json['last_page']);
    lastPageUrl = _asString(json['last_page_url']);
    if (json['links'] != null) {
      links = <Links>[];
      json['links'].forEach((v) {
        links!.add(Links.fromJson(v));
      });
    }
    nextPageUrl = _asString(json['next_page_url']);
    path = _asString(json['path']);
    perPage = _asString(json['per_page']);
    prevPageUrl = _asString(json['prev_page_url']);
    to = _asString(json['to']);
    total = _asString(json['total']);
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['current_page'] = currentPage;
    if (this.data != null) {
      data['data'] = this.data!.map((v) => v.toJson()).toList();
    }
    data['first_page_url'] = firstPageUrl;
    data['from'] = from;
    data['last_page'] = lastPage;
    data['last_page_url'] = lastPageUrl;
    if (links != null) {
      data['links'] = links!.map((v) => v.toJson()).toList();
    }
    data['next_page_url'] = nextPageUrl;
    data['path'] = path;
    data['per_page'] = perPage;
    data['prev_page_url'] = prevPageUrl;
    data['to'] = to;
    data['total'] = total;
    return data;
  }
}

class ProductsData {
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
  String? minPrice;
  String? maxPrice;
  String? averageRating;
  List<Images>? images;
  Category? category;

  ProductsData.fromJson(Map<String, dynamic> json) {
    id = _asString(json['id']);
    nameArabic = _asString(json['name_arabic']);
    nameGerman = _asString(json['name_german']);
    slugArabic = _asString(json['slug_arabic']);
    slugGerman = _asString(json['slug_german']);
    descriptionArabic = _asString(json['description_arabic']);
    descriptionGerman = _asString(json['description_german']);
    categoryId = _asString(json['category_id']);
    isActive = json['is_active'];
    vendorId = _asString(json['vendor_id']);
    createdAt = _asString(json['created_at']);
    updatedAt = _asString(json['updated_at']);
    childCategoryId = _asString(json['child_category_id']);
    minPrice = _asString(json['min_price']);
    maxPrice = _asString(json['max_price']);
    averageRating = _asString(json['average_rating']);

    if (json['images'] != null) {
      images = <Images>[];
      json['images'].forEach((v) {
        images!.add(Images.fromJson(v));
      });
    }
    category = json['category'] != null ? Category.fromJson(json['category']) : null;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name_arabic'] = nameArabic;
    data['name_german'] = nameGerman;
    data['slug_arabic'] = slugArabic;
    data['slug_german'] = slugGerman;
    data['description_arabic'] = descriptionArabic;
    data['description_german'] = descriptionGerman;
    data['category_id'] = categoryId;
    data['is_active'] = isActive;
    data['vendor_id'] = vendorId;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['child_category_id'] = childCategoryId;
    data['min_price'] = minPrice;
    data['max_price'] = maxPrice;
    data['average_rating'] = averageRating;
    if (images != null) {
      data['images'] = images!.map((v) => v.toJson()).toList();
    }
    if (category != null) {
      data['category'] = category!.toJson();
    }
    return data;
  }
}

class Images {
  String? id;
  String? productId;
  String? image;
  String? url;

  Images.fromJson(Map<String, dynamic> json) {
    id = _asString(json['id']);
    productId = _asString(json['product_id']);
    image = _asString(json['image']);
    url = _asString(json['url']);
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'product_id': productId,
    'image': image,
    'url': url,
  };
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

  Category.fromJson(Map<String, dynamic> json) {
    id = _asString(json['id']);
    nameArabic = _asString(json['name_arabic']);
    nameGerman = _asString(json['name_german']);
    slugArabic = _asString(json['slug_arabic']);
    slugGerman = _asString(json['slug_german']);
    image = _asString(json['image']);
    parentId = _asString(json['parent_id']);
    createdAt = _asString(json['created_at']);
    updatedAt = _asString(json['updated_at']);
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'name_arabic': nameArabic,
    'name_german': nameGerman,
    'slug_arabic': slugArabic,
    'slug_german': slugGerman,
    'image': image,
    'parent_id': parentId,
    'created_at': createdAt,
    'updated_at': updatedAt,
  };
}

class Links {
  String? url;
  String? label;
  bool? active;

  Links.fromJson(Map<String, dynamic> json) {
    url = _asString(json['url']);
    label = _asString(json['label']);
    active = json['active'];
  }

  Map<String, dynamic> toJson() => {
    'url': url,
    'label': label,
    'active': active,
  };
}

class Filters {
  List<Categories>? categories;
  List<String>? colors;
  List<String>? sizes;
  PriceRange? priceRange;
  List<SortOptions>? sortOptions;

  Filters.fromJson(Map<String, dynamic> json) {
    if (json['categories'] != null) {
      categories = <Categories>[];
      json['categories'].forEach((v) {
        categories!.add(Categories.fromJson(v));
      });
    }
    colors = json['colors']?.map<String>((e) => _asString(e) ?? '').toList();
    sizes = json['sizes']?.map<String>((e) => _asString(e) ?? '').toList();
    priceRange =
    json['price_range'] != null ? PriceRange.fromJson(json['price_range']) : null;
    if (json['sort_options'] != null) {
      sortOptions = <SortOptions>[];
      json['sort_options'].forEach((v) {
        sortOptions!.add(SortOptions.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() => {
    'categories': categories?.map((v) => v.toJson()).toList(),
    'colors': colors,
    'sizes': sizes,
    'price_range': priceRange?.toJson(),
    'sort_options': sortOptions?.map((v) => v.toJson()).toList(),
  };
}

class Categories {
  String? id;
  String? nameArabic;
  String? nameGerman;
  List<Subcategories>? subcategories;

  Categories.fromJson(Map<String, dynamic> json) {
    id = _asString(json['id']);
    nameArabic = _asString(json['name_arabic']);
    nameGerman = _asString(json['name_german']);
    if (json['subcategories'] != null) {
      subcategories = <Subcategories>[];
      json['subcategories'].forEach((v) {
        subcategories!.add(Subcategories.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'name_arabic': nameArabic,
    'name_german': nameGerman,
    'subcategories': subcategories?.map((v) => v.toJson()).toList(),
  };
}

class Subcategories {
  String? id;
  String? nameArabic;
  String? nameGerman;
  String? slugArabic;
  String? slugGerman;
  String? image;
  String? parentId;
  String? createdAt;
  String? updatedAt;

  Subcategories.fromJson(Map<String, dynamic> json) {
    id = _asString(json['id']);
    nameArabic = _asString(json['name_arabic']);
    nameGerman = _asString(json['name_german']);
    slugArabic = _asString(json['slug_arabic']);
    slugGerman = _asString(json['slug_german']);
    image = _asString(json['image']);
    parentId = _asString(json['parent_id']);
    createdAt = _asString(json['created_at']);
    updatedAt = _asString(json['updated_at']);
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'name_arabic': nameArabic,
    'name_german': nameGerman,
    'slug_arabic': slugArabic,
    'slug_german': slugGerman,
    'image': image,
    'parent_id': parentId,
    'created_at': createdAt,
    'updated_at': updatedAt,
  };
}

class PriceRange {
  String? min;
  String? max;

  PriceRange.fromJson(Map<String, dynamic> json) {
    min = _asString(json['min']);
    max = _asString(json['max']);
  }

  Map<String, dynamic> toJson() => {
    'min': min,
    'max': max,
  };
}

class SortOptions {
  String? key;
  String? nameArabic;
  String? nameGerman;

  SortOptions.fromJson(Map<String, dynamic> json) {
    key = _asString(json['key']);
    nameArabic = _asString(json['name_arabic']);
    nameGerman = _asString(json['name_german']);
  }

  Map<String, dynamic> toJson() => {
    'key': key,
    'name_arabic': nameArabic,
    'name_german': nameGerman,
  };
}
