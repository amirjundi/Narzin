import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';

import '../../../../bussiness_logic/main_hub_cubits/main_hub_cubit.dart';
import '../../../../generated/assets.dart';
import '../../../../generated/l10n.dart';

class CardsScreen extends StatelessWidget {
  const CardsScreen ({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).cards,
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        automaticallyImplyLeading: false,
        leading: IconButton(
          onPressed: () {
            Navigator.canPop(context) ? Navigator.pop(context) : null;
          },
          icon: const Icon(Icons.arrow_back_ios_rounded),
        ),
        actions: [
          IconButton(
            onPressed: () {
              // Navigator.canPop(context) ? Navigator.pop(context) : null;
            },
            icon: const Icon(Icons.more_vert_sharp),
          ),
        ],
        centerTitle: true,
      ),
      body: Container(
        width: ScreenSizing.width,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 5),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Image.asset(Assets.imagesNoCardsYet,height: ScreenSizing.height*0.35,),
            const SizedBox(height: 10,),
            Text(S.of(context).no_cards_message,style: const TextStyle(color: Color(0xff4B5563),fontSize: 16,fontWeight: FontWeight.normal),textAlign: TextAlign.center,),
          ],
        ),
      ),
      bottomNavigationBar: SizedBox(
        height: 60,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20.0),
          child: CustomSignIn_UpOne(
            title: "+ ${S.of(context).add}",
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
