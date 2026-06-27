import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/main_hub_cubits/main_hub_cubit.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';

import '../../../../generated/l10n.dart';

class MerchantSuccessScreen extends StatelessWidget {
  const MerchantSuccessScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        width: ScreenSizing.width,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 5),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Image.asset(Assets.imagesSuccess,height: ScreenSizing.height*0.35,),
            Column(
              mainAxisAlignment: MainAxisAlignment.center,
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                Text(S.of(context).congratulations,style: const TextStyle(color: Color(0xff4B5563),fontSize: 16,fontWeight: FontWeight.normal),textAlign: TextAlign.justify,),
                Text(S.of(context).request_successfully_sent,style: const TextStyle(fontSize: 18,fontWeight: FontWeight.bold),),
              ],
            ),
            const SizedBox(height: 10,),
            Text(S.of(context).thank_you_message,style: const TextStyle(color: Color(0xff4B5563),fontSize: 16,fontWeight: FontWeight.normal),textAlign: TextAlign.center,),
          ],
        ),
      ),
      bottomNavigationBar: SizedBox(
        height: 60,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20.0),
          child: CustomSignIn_UpOne(
            title: S.of(context).go_to_home,
            ontap: () {
              Navigator.popUntil(context, (route) => route.isFirst,);
              BlocProvider.of<MainHubCubit>(context).setCurrentIndex(0);
            },
          ),
        ),
      ),
    );
  }
}
