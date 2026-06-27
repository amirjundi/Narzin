import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

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
      padding: EdgeInsets.symmetric(vertical: 7, horizontal: 7),
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
                    child: CachedNetworkImage(imageUrl: productImage??'',
                      fit: BoxFit.cover,
                      placeholder: (context, url) => Center(child: CircularProgressIndicator(),),
                      errorWidget: (context, url, error) => Image.asset(Assets.imagesProductPlaceholder2,fit: BoxFit.cover,),
                    ),
                  ),
                ),
                Positioned(
                  top: 0,
                  right: 0,
                  child: IconButton(
                    style: IconButton.styleFrom(
                      backgroundColor: Color(0xffffffff),
                      padding: EdgeInsets.zero,
                      maximumSize: Size(37, 37),
                      minimumSize: Size(37, 37),
                    ),
                    padding: EdgeInsets.zero,
                    onPressed: onPressed,
                    icon:IconWidget?? Icon(
                      icon,
                      size: 25,
                      color: Colors.red,
                    ),
                  ),
                ),
              ],
            ),
          ),
          SizedBox(
            height: 10,
          ),
          Row(
            children: [
              SizedBox(
                width: 5,
              ),
              Expanded(
                child: Text(
                  productName??'',
                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600,overflow: TextOverflow.ellipsis),
                ),
              ),
              Row(
                children: [
                  Image.asset(
                    Assets.appIconsRateIcon,
                    height: 15,
                  ),
                  SizedBox(
                    width: 5,
                  ),
                  Text(rating??'4.7', style: TextStyle(fontSize: 12,))
                ],
              ),
              SizedBox(
                width: 5,
              ),
            ],
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 5),
            child: Text(
              category??'Category',
              style: TextStyle(fontSize: 14, color: Colors.grey[600],overflow: TextOverflow.ellipsis),
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
                        'EUR',
                        style: TextStyle(fontSize: 16, color: Constants.mainColor, fontWeight: FontWeight.w600,overflow: TextOverflow.ellipsis),
                      ),
                      Expanded(
                        child: Text(
                          '${priceTo??priceFrom??0.00}',
                          style: TextStyle(fontSize: 16, color: Constants.mainColor, fontWeight: FontWeight.w600,overflow: TextOverflow.ellipsis),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              Expanded(
                flex: 6,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 0),
                  child: priceTo != null?
                  Row(
                    mainAxisAlignment: MainAxisAlignment.start,
                    children: [
                      Text(
                        'EUR',
                        style: TextStyle(fontSize: 14, color: Constants.grey, fontWeight: FontWeight.w400, decoration: TextDecoration.lineThrough,overflow: TextOverflow.ellipsis),
                      ),
                      Expanded(
                        child: Text(
                          '${priceFrom??0.00}',
                          style: TextStyle(fontSize: 14, color: Constants.grey, fontWeight: FontWeight.w400, decoration: TextDecoration.lineThrough,overflow: TextOverflow.ellipsis),
                        ),
                      ),

                    ],
                  ):
                  null,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}