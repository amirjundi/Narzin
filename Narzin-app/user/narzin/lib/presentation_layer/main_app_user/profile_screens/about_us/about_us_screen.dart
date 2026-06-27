import 'package:flutter/material.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/assets.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';

class AboutUsScreen extends StatelessWidget {
  const AboutUsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).who_we_are,
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
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Expanded(
              child: SingleChildScrollView(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Image.asset(Assets.imagesAboutUsAsset,height: ScreenSizing.height*0.35,),
                    Text(S.of(context).why_choose_us,style: const TextStyle(fontSize: 18,fontWeight: FontWeight.bold),),
                    const SizedBox(height: 10,),
                    Text(S.of(context).spiritual_experience,style: const TextStyle(color: Color(0xff4B5563),fontSize: 16,fontWeight: FontWeight.normal),textAlign: TextAlign.justify,),
                    const SizedBox(height: 20,),
                    Text(S.of(context).continuous_motivation,style: const TextStyle(color: Color(0xff4B5563),fontSize: 16,fontWeight: FontWeight.normal),textAlign: TextAlign.justify,),
                    const SizedBox(height: 20,),
                    Text(S.of(context).simple_interface,style: const TextStyle(color: Color(0xff4B5563),fontSize: 16,fontWeight: FontWeight.normal),textAlign: TextAlign.justify,),
                    const SizedBox(height: 20,),
                    Text(S.of(context).inspiring_community,style: const TextStyle(color: Color(0xff4B5563),fontSize: 16,fontWeight: FontWeight.normal),textAlign: TextAlign.justify,),
                    const SizedBox(height: 20,),
                    Text(S.of(context).detailed_statistics,style: const TextStyle(color: Color(0xff4B5563),fontSize: 16,fontWeight: FontWeight.normal),textAlign: TextAlign.justify,),
                    const SizedBox(height: 20,),
                    Text(S.of(context).not_just_app,style: const TextStyle(color: Color(0xff4B5563),fontSize: 16,fontWeight: FontWeight.normal),textAlign: TextAlign.justify,),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
      bottomNavigationBar: SizedBox(
        height: 60,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 20.0),
          child: CustomSignIn_UpTwo(
            title: S.of(context).contact_us,
            ontap: () {

            },
          ),
        ),
      ),
    );
  }
}
