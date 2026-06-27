import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_colorpicker/flutter_colorpicker.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_manipulation_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/model_layer/attributes_model.dart';
import 'package:narzin/model_layer/single_produt_model.dart';
import 'package:narzin/presentation_layer/main_app_vendor/products_screens/products_Forms/edit_product_screens/image_variant_update_screen.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/text_form_fields/custom_input_decorator.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field.dart';

import '../../../../../generated/l10n.dart';

class EditProductDetailsScreen extends StatelessWidget {
  EditProductDetailsScreen({super.key,required this.product});

  final SingleProductModel product;
  static final _key = GlobalKey<FormState>();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).add_product,
          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
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
      body: SafeArea(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
          height: ScreenSizing.height,
          width: ScreenSizing.width,
          child: SingleChildScrollView(
            child: Form(
              key: _key,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                mainAxisAlignment: MainAxisAlignment.start,
                children: [
                  Stack(
                    children: [
                      SizedBox(
                        height: 50,
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const SizedBox(
                              width: 30,
                            ),
                            const DoneNodeWidget(),
                            Expanded(
                              child: SizedBox(
                                  height: 25,
                                  child: Divider(
                                    color: Constants.mainColor,
                                  )),
                            ),
                            Expanded(
                              child: SizedBox(
                                  height: 25,
                                  child: Divider(
                                    color: Constants.mainColor,
                                  )),
                            ),
                            const CurrentNodeWidget(),
                            const SizedBox(
                              width: 30,
                            ),
                          ],
                        ),
                      ),
                      Positioned(
                        bottom: 0,
                        right: 0,
                        child: Text(S.of(context).basic_data),
                      ),
                      Positioned(
                        bottom: 0,
                        left: 20,
                        child: Text(S.of(context).details),
                      ),
                    ],
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                    builder: (context, state) {
                      var variantsToPost = context.read<ProductManipulationCubit>().variantsToPost;
                      if(variantsToPost.isEmpty){
                        return const SizedBox();
                      }
                      return Container(
                        height: 50,
                        margin: const EdgeInsets.symmetric(vertical: 10),
                        width: ScreenSizing.width,
                        child: Row(
                          children: [
                            Expanded(
                              child: ListView.separated(
                                scrollDirection: Axis.horizontal,
                                itemBuilder: (context, index) {
                                  return Stack(
                                    children: [
                                      Container(
                                        height: 60,
                                        constraints: const BoxConstraints(minWidth: 120),
                                      ),
                                      InkWell(
                                        onTap: () {
                                          context.read<ProductManipulationCubit>().setSelectedVariantIndex(index);
                                          context.read<ProductManipulationCubit>().reGenerateAttributes(variantsToPost[index]);
                                        },
                                        child: Container(
                                          height: 40,
                                          padding: const EdgeInsets.symmetric(horizontal: 10),
                                          constraints: const BoxConstraints(minWidth: 100),
                                          decoration: BoxDecoration(
                                            borderRadius: BorderRadius.circular(20),
                                            color: Color(0xfffff811 ~/ (5 + index)),
                                          ),
                                          margin: const EdgeInsets.symmetric(vertical: 5),
                                          child: Center(
                                            child: Text('Variant ${index + 1}'),
                                          ),
                                        ),
                                      ),
                                      Positioned(
                                        top: 0,
                                        left: 0,
                                        child: InkWell(
                                          onTap: () {
                                            context.read<ProductManipulationCubit>().deleteVariantIndex(index);
                                          },
                                          child: const Icon(
                                            Icons.cancel,
                                          ),
                                        ),
                                      )
                                    ],
                                  );
                                },
                                separatorBuilder: (context, index) => const SizedBox(
                                  width: 10,
                                ),
                                itemCount: variantsToPost.length,
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                  BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                    builder: (context, state) {
                      // context.read<ProductManipulationCubit>().buildForm(context);
                      DateTime? selectedDate = context.read<ProductManipulationCubit>().pickedExpiryDate;
                      bool isExpiryDateSelected = context.read<ProductManipulationCubit>().isExpiryDateSelected;
                      bool isExpiryDaysSelected = context.read<ProductManipulationCubit>().isExpiryDaysSelected;
                      return Column(
                        children: [
                          ...context.read<ProductManipulationCubit>().buildForm(context),
                          CustomTextFormField(
                            title: S.of(context).selling_price,
                            hint: S.of(context).selling_price,
                            controller: context.read<ProductManipulationCubit>().price,
                            inputFormatters: [
                              FilteringTextInputFormatter.digitsOnly
                            ],
                            keyboardType: TextInputType.number,
                          ),
                          const SizedBox(
                            height: 20,
                          ),
                          CustomTextFormField(
                            title: S.of(context).quantity,
                            hint: S.of(context).quantity,
                            controller: context.read<ProductManipulationCubit>().stock,
                            isEnabled: true,
                            onChanged: (value) {

                            },
                            inputFormatters: [
                              FilteringTextInputFormatter.digitsOnly
                            ],
                            keyboardType: TextInputType.number,
                          ),
                          const SizedBox(
                            height: 20,
                          ),
                          CustomTextFormField(
                            title: S.of(context).cost,
                            hint: S.of(context).cost,
                            controller: context.read<ProductManipulationCubit>().cost,
                            isEnabled: true,
                            onChanged: (value) {

                            },
                            inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                            keyboardType: TextInputType.number,
                          ),
                          SizedBox(
                            height:isExpiryDateSelected?0: 20,
                          ),
                          isExpiryDateSelected? Container(): CustomTextFormField(
                            title: S.of(context).expiry_days,
                            hint: S.of(context).expiry_days,
                            controller: context.read<ProductManipulationCubit>().expiryDays,
                            isEnabled: true,
                            onChanged: (value) {
                              context.read<ProductManipulationCubit>().setIsExpiryDaysSelected();
                              context.read<ProductManipulationCubit>().setIsExpiryDateSelected();
                            },
                            inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                            keyboardType: TextInputType.number,
                          ),
                          SizedBox(
                            height: isExpiryDaysSelected?0:20,
                          ),
                          isExpiryDaysSelected?Container(): InkWell(
                            onTap: () {
                              context.read<ProductManipulationCubit>().selectExpiryDate(context);
                              context.read<ProductManipulationCubit>().setIsExpiryDaysSelected();
                              context.read<ProductManipulationCubit>().setIsExpiryDateSelected();
                            },
                            child: CustomInputDecorator(
                              title: S.of(context).expiry_date,
                              hint: 'DD/MM/YYYY',
                              suffix: const Icon(Icons.calendar_month_sharp),
                              child: Padding(
                                padding: const EdgeInsets.symmetric(horizontal: 5.0),
                                child: Text(
                                  selectedDate != null ? '${selectedDate.day}/${selectedDate.month}/${selectedDate.year}' : 'DD/MM/YYYY',
                                  style: const TextStyle(fontSize: 16, color: Colors.grey),
                                ),
                              ),
                            ),
                          ),
                        ],
                      );
                    },
                  ),
                  const SizedBox(
                    height: 40,
                  ),
                  BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                    builder: (context, state) {
                      return InkWell(
                        onTap: () {
                          int index = context.read<ProductManipulationCubit>().selectedVariantIndex;
                          if(index == -1) {
                            context.read<ProductManipulationCubit>().formulateVariant();
                          }else{
                            context.read<ProductManipulationCubit>().formulateIndexedVariant(index);
                          }

                        },
                        child: Container(
                          height: 50,
                          width: ScreenSizing.width,
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(10),
                            border: Border.all(color: Colors.grey[300]!),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: [
                              Icon(
                                Icons.add,
                                color: Colors.grey[500]!,
                              ),
                              Text(
                                S.of(context).add,
                                style: TextStyle(color: Colors.grey[500]!, fontSize: 18),
                              )
                            ],
                          ),
                        ),
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.symmetric(vertical: 0, horizontal: 15),
        child: BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
          builder: (context, state) {
            bool isLoading = context.read<ProductManipulationCubit>().isLoading;
            return CustomSignIn_UpOne(
              title: S.of(context).next,
              customizeChild:isLoading?const Center(child: CircularProgressIndicator(),): Text(
                S.of(context).next,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                  color: Colors.white,
                ),
              ),
              ontap: isLoading? null: () async {
                Navigator.push(context, MaterialPageRoute(builder: (context) => ImageVariantUpdateScreen(product: product,),));
              },
            );
          },
        ),
      ),
    );
  }
}



class CurrentNodeWidget extends StatelessWidget {
  const CurrentNodeWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 25,
      height: 25,
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(100), border: Border.all(color: Constants.mainColor)),
      child: Center(
        child: Container(
          width: 5,
          height: 5,
          decoration: BoxDecoration(color: Constants.mainColor, borderRadius: BorderRadius.circular(100), border: Border.all(color: Constants.mainColor)),
        ),
      ),
    );
  }
}

class DoneNodeWidget extends StatelessWidget {
  const DoneNodeWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 25,
      height: 25,
      decoration: BoxDecoration(color: Constants.mainColor, borderRadius: BorderRadius.circular(100), border: Border.all(color: Constants.mainColor)),
      child: const Center(
        child: Icon(
          Icons.check,
          size: 15,
          color: Colors.white,
          weight: 10,
        ),
      ),
    );
  }
}

class NotReachedNodeWidget extends StatelessWidget {
  const NotReachedNodeWidget({
    super.key,
  });

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 25,
      height: 25,
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(100), border: Border.all(color: Colors.grey[300]!)),
    );
  }
}
