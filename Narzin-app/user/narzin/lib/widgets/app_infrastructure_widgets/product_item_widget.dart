import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:narzin/core/screen_sizing_constants.dart';

import '../../core/constants.dart';
import '../../generated/assets.dart';

class ProductItem extends StatelessWidget {
  const ProductItem({
    super.key,
    this.productName,
    this.productImage,
    this.category,
    this.rating,
    this.priceFrom,
    this.priceTo,
    this.onIconPressed,
    this.icon,
    this.IconWidget,
    this.onTap,
  });

  final IconData? icon;
  final Widget? IconWidget;

  final String? productName;

  final String? productImage;

  final String? category;

  final String? rating;

  final String? priceFrom;

  final String? priceTo;

  final void Function()? onIconPressed;
  final void Function()? onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 7),
        width: 160,
        decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.grey[200]!)),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            SizedBox(
              height: 140,
              child: Stack(
                fit: StackFit.expand,
                alignment: Alignment.center,
                children: [
                  Container(
                    height: 140,
                    decoration: BoxDecoration(
                      color: Colors.grey[500],
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(10),
                      child: CachedNetworkImage(
                        imageUrl: productImage ?? '',
                        fit: BoxFit.cover,
                        placeholder: (context, url) => const Center(
                          child: CircularProgressIndicator(),
                        ),
                        errorWidget: (context, url, error) => Image.asset(
                          Assets.imagesProductPlaceholder2,
                          fit: BoxFit.cover,
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    top: 0,
                    right: 0,
                    child: IconButton(
                      style: IconButton.styleFrom(
                        backgroundColor: const Color(0xffffffff),
                        padding: EdgeInsets.zero,
                        maximumSize: const Size(37, 37),
                        minimumSize: const Size(37, 37),
                      ),
                      padding: EdgeInsets.zero,
                      onPressed: onIconPressed,
                      icon: IconWidget ??
                          Icon(
                            icon,
                            size: 25,
                            color: Colors.red,
                          ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(
              height: 10,
            ),
            Row(
              children: [
                const SizedBox(
                  width: 5,
                ),
                Expanded(
                  child: Text(
                    productName ?? '',
                    style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, overflow: TextOverflow.ellipsis),
                  ),
                ),
                Row(
                  children: [
                    Image.asset(
                      Assets.appIconsRateIcon,
                      height: 15,
                    ),
                    const SizedBox(
                      width: 5,
                    ),
                    Text(rating ?? '4.7',
                        style: const TextStyle(
                          fontSize: 12,
                        ))
                  ],
                ),
                const SizedBox(
                  width: 5,
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 5),
              child: Text(
                category ?? 'Category',
                style: TextStyle(fontSize: 14, color: Colors.grey[600], overflow: TextOverflow.ellipsis),
              ),
            ),
            (priceFrom == null) && (priceTo == null)
                ? Container()
                : Row(
                    children: [
                      Expanded(
                        flex: 5,
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 5),
                          child: Row(
                            children: [
                              Text(
                                'EUR ',
                                style: TextStyle(fontSize: 16, color: Constants.mainColor, fontWeight: FontWeight.w600, overflow: TextOverflow.ellipsis),
                              ),
                              Expanded(
                                child: Text(
                                  '${priceTo ?? priceFrom ?? 0.00}',
                                  style: TextStyle(fontSize: 16, color: Constants.mainColor, fontWeight: FontWeight.w600, overflow: TextOverflow.ellipsis),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      priceTo != null
                          ? Expanded(
                              flex: 6,
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 0),
                                child: Row(
                                  mainAxisAlignment: MainAxisAlignment.start,
                                  children: [
                                    Text(
                                      'EUR ',
                                      style: TextStyle(fontSize: 14, color: Constants.grey, fontWeight: FontWeight.w400, decoration: TextDecoration.lineThrough, overflow: TextOverflow.ellipsis),
                                    ),
                                    Expanded(
                                      child: Text(
                                        '${priceFrom ?? 0.00}',
                                        style: TextStyle(fontSize: 14, color: Constants.grey, fontWeight: FontWeight.w400, decoration: TextDecoration.lineThrough, overflow: TextOverflow.ellipsis),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            )
                          : Container(),
                    ],
                  ),
          ],
        ),
      ),
    );
  }
}

class CartItem extends StatelessWidget {
  const CartItem({
    super.key,
    required this.locale,
    this.productName,
    this.productImage,
    this.priceFrom,
    this.priceTo,
    this.onIconPressed,
    this.icon,
    this.IconWidget,
    this.onIncrease,
    this.onDecrease,
    this.quantity,
    this.onDelete,
    this.onTap,
    this.quantityWidget,
    this.deleteIcon,
    this.isOutOfStock,
  });

  final IconData? icon;
  final Widget? IconWidget;
  final Widget? quantityWidget;
  final Widget? deleteIcon;

  final String? productName;
  final bool? isOutOfStock;

  final String? productImage;

  final String? priceFrom;

  final String? priceTo;
  final String? quantity;
  final String locale;

  final void Function()? onIconPressed;
  final void Function()? onTap;
  final void Function()? onIncrease;
  final void Function()? onDecrease;
  final void Function()? onDelete;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 7),
        width: 160,
        height: 160,
        decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.grey[200]!)),
        child: Stack(
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(
                      height: 140,
                      width: 140,
                      child: Stack(
                        fit: StackFit.expand,
                        alignment: Alignment.center,
                        children: [
                          Container(
                            height: 140,
                            width: 140,
                            decoration: BoxDecoration(
                              color: Colors.grey[500],
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: ClipRRect(
                              borderRadius: BorderRadius.circular(10),
                              child: CachedNetworkImage(
                                imageUrl: productImage ?? '',
                                fit: BoxFit.cover,
                                placeholder: (context, url) => const Center(
                                  child: CircularProgressIndicator(),
                                ),
                                errorWidget: (context, url, error) => Image.asset(
                                  Assets.imagesProductPlaceholder2,
                                  fit: BoxFit.cover,
                                ),
                              ),
                            ),
                          ),
                          // Positioned(
                          //   top: 0,
                          //   right: 0,
                          //   child: IconButton(
                          //     style: IconButton.styleFrom(
                          //       backgroundColor: const Color(0xffffffff),
                          //       padding: EdgeInsets.zero,
                          //       maximumSize: const Size(37, 37),
                          //       minimumSize: const Size(37, 37),
                          //     ),
                          //     padding: EdgeInsets.zero,
                          //     onPressed: onIconPressed,
                          //     icon: IconWidget ??
                          //         Icon(
                          //           icon,
                          //           size: 25,
                          //           color: Colors.red,
                          //         ),
                          //   ),
                          // ),
                        ],
                      ),
                    ),
                    const SizedBox(
                      width: 10,
                    ),
                    Expanded(
                      child: Column(
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  productName ?? 'xxxxxxxxxxxxxxxx',
                                  style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, overflow: TextOverflow.ellipsis),
                                ),
                              ),

                            ],
                          ),
                          const SizedBox(
                            height: 10,
                          ),
                          Row(
                            children: [
                              Expanded(
                                flex: 5,
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 5),
                                  child: Row(
                                    children: [
                                      Text(
                                        'EUR ',
                                        style: TextStyle(fontSize: 16, color: Constants.mainColor, fontWeight: FontWeight.w600, overflow: TextOverflow.ellipsis),
                                      ),
                                      Expanded(
                                        child: Text(
                                          '${priceTo ?? priceFrom ?? 0.00}',
                                          style: TextStyle(fontSize: 16, color: Constants.mainColor, fontWeight: FontWeight.w600, overflow: TextOverflow.ellipsis),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                              priceTo != null
                                  ? Expanded(
                                      flex: 6,
                                      child: Padding(
                                        padding: const EdgeInsets.symmetric(horizontal: 0),
                                        child: Row(
                                          mainAxisAlignment: MainAxisAlignment.start,
                                          children: [
                                            Text(
                                              'EUR ',
                                              style: TextStyle(fontSize: 14, color: Constants.grey, fontWeight: FontWeight.w400, decoration: TextDecoration.lineThrough, overflow: TextOverflow.ellipsis),
                                            ),
                                            Expanded(
                                              child: Text(
                                                '${priceFrom ?? 0.00}',
                                                style: TextStyle(fontSize: 14, color: Constants.grey, fontWeight: FontWeight.w400, decoration: TextDecoration.lineThrough, overflow: TextOverflow.ellipsis),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    )
                                  : Container(),
                            ],
                          ),
                          const SizedBox(
                            height: 10,
                          ),
                          Row(
                            children: [
                              IconButton(
                                style: IconButton.styleFrom(
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(100)),
                                  backgroundColor: Constants.mainColor,
                                  maximumSize: const Size(40, 40),
                                  minimumSize: const Size(40, 40),
                                ),
                                onPressed: onIncrease,
                                icon: const Icon(
                                  Icons.add,
                                  color: Colors.white,
                                ),
                              ),
                              const SizedBox(
                                width: 10,
                              ),
                              quantityWidget ??
                                  Center(
                                    child: Text(
                                      quantity ?? '0',
                                      style: const TextStyle(fontSize: 19),
                                    ),
                                  ),
                              const SizedBox(
                                width: 10,
                              ),
                              IconButton(
                                style: IconButton.styleFrom(
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(100)),
                                  backgroundColor: Constants.lightSecondaryColor,
                                  maximumSize: const Size(40, 40),
                                  minimumSize: const Size(40, 40),
                                ),
                                onPressed: onDecrease,
                                icon: Icon(
                                  Icons.remove,
                                  color: Constants.mainColor,
                                ),
                              ),
                            ],
                          )
                        ],
                      ),
                    ),
                  ],
                ),
              ],
            ),
            isOutOfStock == true
                ? Container(
                    height: 160,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(10),
                      color: Colors.black38,
                    ),
                    width: ScreenSizing.width,
              alignment: Alignment.center,
              child: const Center(child: Text("Out Of Stock!",style: TextStyle(color: Colors.white,fontSize: 20,fontWeight: FontWeight.bold),)),
                  )
                : Container(),
            Positioned(
              top: 0,
              right: locale != 'ar'?0:null,
              left: locale == 'ar'?0:null,
              child: IconButton(
                  onPressed: onDelete,
                  icon: deleteIcon ??
                      const Icon(
                        Icons.delete,
                        color: Colors.red,
                      )),
            ),
          ],
        ),
      ),
    );
  }
}

class ProductItem2 extends StatelessWidget {
  const ProductItem2({
    super.key,
    this.productName,
    this.productImage,
    this.category,
    this.rating,
    this.priceFrom,
    this.priceTo,
    this.onPressed,
    this.icon,
    this.IconWidget,
  });

  final IconData? icon;
  final Widget? IconWidget;

  final String? productName;

  final String? productImage;

  final String? category;

  final String? rating;

  final String? priceFrom;

  final String? priceTo;

  final void Function()? onPressed;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 7, horizontal: 7),
      width: 200,
      decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: Colors.grey[200]!)),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          SizedBox(
            height: 140,
            child: Stack(
              fit: StackFit.expand,
              alignment: Alignment.center,
              children: [
                Container(
                  height: 140,
                  decoration: BoxDecoration(
                    color: Colors.grey[500],
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(10),
                    child: CachedNetworkImage(
                      imageUrl: productImage ?? '',
                      fit: BoxFit.cover,
                      placeholder: (context, url) => const Center(
                        child: CircularProgressIndicator(),
                      ),
                      errorWidget: (context, url, error) => Image.asset(
                        Assets.imagesProductPlaceholder2,
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                ),
                Positioned(
                  bottom: 0,
                  left: 0,
                  child: IconButton(
                    style: IconButton.styleFrom(
                      backgroundColor: const Color(0xffffffff),
                      padding: EdgeInsets.zero,
                      maximumSize: const Size(37, 37),
                      minimumSize: const Size(37, 37),
                    ),
                    padding: EdgeInsets.zero,
                    onPressed: onPressed,
                    icon: IconWidget ??
                        Icon(
                          icon,
                          size: 25,
                          color: Colors.red,
                        ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(
            height: 10,
          ),
          Expanded(
            child: Row(
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Row(
                        children: [
                          const SizedBox(
                            width: 5,
                          ),
                          Expanded(
                            child: Text(
                              productName ?? '',
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, overflow: TextOverflow.ellipsis),
                            ),
                          ),
                          const SizedBox(
                            width: 5,
                          ),
                        ],
                      ),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 5),
                        child: Text(
                          category ?? 'Category',
                          style: TextStyle(fontSize: 14, color: Colors.grey[600], overflow: TextOverflow.ellipsis),
                        ),
                      ),
                    ],
                  ),
                ),
                Icon(
                  Icons.arrow_forward_rounded,
                  color: Constants.mainColor,
                  size: 23,
                )
              ],
            ),
          ),
          Row(
            children: [
              Expanded(
                flex: 5,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 5),
                  child: Row(
                    children: [
                      Text(
                        'EUR ',
                        style: TextStyle(fontSize: 16, color: Constants.mainColor, fontWeight: FontWeight.w600, overflow: TextOverflow.ellipsis),
                      ),
                      Expanded(
                        child: Text(
                          '${priceTo ?? priceFrom ?? 0.00}',
                          style: TextStyle(fontSize: 16, color: Constants.mainColor, fontWeight: FontWeight.w600, overflow: TextOverflow.ellipsis),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              priceTo == null?Container():Expanded(
                flex: 6,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 0),
                  child: priceTo != null
                      ? Row(
                          mainAxisAlignment: MainAxisAlignment.start,
                          children: [
                            Text(
                              'EUR ',
                              style: TextStyle(fontSize: 14, color: Constants.grey, fontWeight: FontWeight.w400, decoration: TextDecoration.lineThrough, overflow: TextOverflow.ellipsis),
                            ),
                            Expanded(
                              child: Text(
                                '${priceFrom ?? 0.00}',
                                style: TextStyle(fontSize: 14, color: Constants.grey, fontWeight: FontWeight.w400, decoration: TextDecoration.lineThrough, overflow: TextOverflow.ellipsis),
                              ),
                            ),
                          ],
                        )
                      : null,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
