import 'package:flutter/material.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/l10n.dart';

import '../../generated/assets.dart';

class OrderItem extends StatelessWidget {
  const OrderItem({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      width: ScreenSizing.width,
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
          color: Colors.grey[300]!,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(S.of(context).order_number),
              const Icon(
                Icons.arrow_forward_ios_rounded,
                size: 15,
              ),
            ],
          ),
          const SizedBox(
            height: 5,
          ),
          Text(
            S.of(context).products,
            textAlign: TextAlign.left,
            style: const TextStyle(fontSize: 13),
          ),
          const SizedBox(
            height: 5,
          ),
          for (int i = 0; i < 3; i++)
            const Padding(
              padding: EdgeInsets.only(bottom: 8.0),
              child: Row(
                children: [
                  Text('جنيه مصري xxx'),
                  Spacer(),
                  Text('1x'),
                  Spacer(),
                  Text('إسم المنتج'),
                ],
              ),
            ),
          const SizedBox(
            height: 10,
          ),
          const Row(
            children: [
              Text(
                'جنيه مصري xxx',
                style: TextStyle(fontWeight: FontWeight.w600),
              ),
              Spacer(),
              Text(
                'الإجمالي',
                style: TextStyle(fontWeight: FontWeight.w600),
              ),
            ],
          ),
          const SizedBox(
            height: 10,
          ),
          Row(
            children: [
              const Row(
                children: [
                  Icon(Icons.access_time_outlined,size: 20,color: Colors.grey,),
                  SizedBox(
                    width: 5,
                  ),
                  Text(
                    'DD/MM - HH:MM',
                    style: TextStyle(fontWeight: FontWeight.w600,fontSize: 10,color: Colors.grey),
                  ),
                ],
              ),
              const Spacer(),
              Row(
                children: [
                  Image.asset(Assets.imagesSuccessPayment1,width: 20,),
                  Text(
                    'الدفع عند الاستلام',
                    style: TextStyle(fontWeight: FontWeight.w600,color: Constants.mainColor),
                  ),
                ],
              ),
            ],
          ),
        ],
      ),
    );
  }
}