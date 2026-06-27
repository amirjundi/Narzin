import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_decorated_container/flutter_decorated_container.dart';
// import 'package:html_editor_enhanced/html_editor.dart';

import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_manipulation_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/core/validations.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/model_layer/categories_model.dart';
import 'package:narzin/presentation_layer/main_app_vendor/products_screens/products_Forms/add_product_screens/details_product_addition_screen.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/image_widgets/insta_image_widget.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field.dart';
// import 'package:quill_html_editor/quill_html_editor.dart';
import '../../../../../widgets/text_form_fields/custom_input_decorator.dart';

class AddProductScreen extends StatelessWidget {
  const AddProductScreen({super.key});

  static final formKey = GlobalKey<FormState>();


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
              key: formKey,
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
                            const CurrentNodeWidget(),
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
                                    color: Colors.grey[300],
                                  )),
                            ),
                            const NotReachedNodeWidget(),
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
                      return CustomTextFormField(
                        controller: context.read<ProductManipulationCubit>().arabicName,
                        title: S.of(context).name_ar,
                        hint: S.of(context).name_ar_placeholder,
                        validator: (p0) {
                          return validateEmptyField(p0);
                        },
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                    builder: (context, state) {
                      return CustomTextFormField(
                        controller: context.read<ProductManipulationCubit>().germanName,
                        title: S.of(context).name_de,
                        hint: S.of(context).name_de_placeholder,
                        validator: (p0) {
                          return validateEmptyField(p0);
                        },
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                    builder: (context, state) {
                      var items = context.read<ProductManipulationCubit>().categories?.data;
                      var SelectedItem = context.read<ProductManipulationCubit>().selectedCategory;
                      return CustomInputDecorator(
                        title: S.of(context).category,
                        hint: 'xxxx',
                        child: SizedBox(
                          height: 40,
                          child: DropdownButton<CategoryData?>(
                            hint: const Padding(
                              padding: EdgeInsets.symmetric(horizontal: 10),
                              child: Text('xxxxx'),
                            ),
                            underline: const SizedBox(),
                            isExpanded: true,
                            items: items
                                ?.map(
                                  (e) => DropdownMenuItem<CategoryData?>(
                                    value: e,
                                    child: Text(BlocProvider.of<LocalizationCubit>(context).locale == 'ar' ? e.nameArabic ?? '' : e.nameGerman ?? ''),
                                  ),
                                )
                                .toList(),
                            value: SelectedItem,
                            onChanged: (value) {
                              context.read<ProductManipulationCubit>().setSelectedCategory(value);
                            },
                          ),
                        ),
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                    builder: (context, state) {
                      var items = context.read<ProductManipulationCubit>().subCategories;
                      var SelectedItem = context.read<ProductManipulationCubit>().selectedSubCategory;
                      return CustomInputDecorator(
                        title: S.of(context).sub_category,
                        hint: 'xxxx',
                        child: SizedBox(
                          height: 40,
                          child: DropdownButton<SubCategories?>(
                            hint: const Padding(
                              padding: EdgeInsets.symmetric(horizontal: 10),
                              child: Text('xxxxx'),
                            ),
                            underline: const SizedBox(),
                            isExpanded: true,
                            items: items.map(
                                  (e) => DropdownMenuItem<SubCategories?>(
                                value: e,
                                child: Text(BlocProvider.of<LocalizationCubit>(context).locale == 'ar' ? e.nameArabic ?? '' : e.nameGerman ?? ''),
                              ),
                            )
                                .toList(),
                            value: SelectedItem,
                            onChanged: (value) {
                              context.read<ProductManipulationCubit>().setSelectedSubCategory(value);
                            },
                          ),
                        ),
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                    builder: (context, state) {
                      return CustomDescFormField(
                        controller: context.read<ProductManipulationCubit>().arabicDesc,
                        title: S.of(context).details_ar,
                        hint: S.of(context).product_details_ar,
                        validator: (p0) {
                          return validateEmptyField(p0);
                        },
                      );
                    },
                  ),
                  const SizedBox(
                    height: 20,
                  ),
                  BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                    builder: (context, state) {
                      return CustomDescFormField(
                        controller: context.read<ProductManipulationCubit>().germanDesc,
                        title: S.of(context).details_de,
                        hint: S.of(context).product_details_de,
                        validator: (p0) {
                          return validateEmptyField(p0);
                        },
                      );
                    },
                  ),

                  const SizedBox(
                    height: 20,
                  ),

                  BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                    builder: (context, state) {
                      return CustomTextFormField(
                        controller: context.read<ProductManipulationCubit>().weight,
                        title: S.of(context).weight,
                        hint: '0.5',
                        keyboardType: TextInputType.number,
                        validator: (p0) {
                          return validateEmptyField(p0);
                        },
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
      bottomNavigationBar: BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
        builder: (context, state) {
          bool isLoading = context.read<ProductManipulationCubit>().isLoading;
          return Padding(
            padding: const EdgeInsets.symmetric(vertical: 0, horizontal: 15),
            child: CustomSignIn_UpOne(
              title: S.of(context).next,
              customizeChild: isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : Text(
                      S.of(context).next,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w700,
                        color: Colors.white,
                      ),
                    ),
              ontap: isLoading
                  ? null
                  : () async {
                      if (formKey.currentState!.validate()) {
                        var res = await context.read<ProductManipulationCubit>().getAttributes();
                        context.read<ProductManipulationCubit>().initializeAttributesForm();
                        context.read<ProductManipulationCubit>().variantsToPost.clear();
                        context.read<ProductManipulationCubit>().resetVariantsForm();
                        // context.read<ProductManipulationCubit>().resetVariantsForm();

                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (context) => BlocProvider.value(
                              value: context.read<ProductManipulationCubit>(),
                              child: DetailsProductAdditionScreen(),
                            ),
                          ),
                        );
                      }
                    },
            ),
          );
        },
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
          size: 17,
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


/**

    Text(
    S.of(context).details_ar,
    style: const TextStyle(fontWeight: FontWeight.w500, fontSize: 15),
    ),
    const SizedBox(
    height: 10,
    ),
    BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
    builder: (context, state) {
    return Container(
    height: 300,
    width: ScreenSizing.width,
    decoration: BoxDecoration(
    border: Border.all(color: Constants.grey),
    borderRadius: BorderRadius.circular(10),
    ),
    child: ClipRRect(
    borderRadius: BorderRadius.circular(10),
    child: Column(
    children: [
    Expanded(
    child: HtmlEditor(
    htmlEditorOptions: HtmlEditorOptions(shouldEnsureVisible: true, hint: S.of(context).product_details_ar, spellCheck: true, autoAdjustHeight: true, adjustHeightForKeyboard: true),
    controller: controller, //required
    htmlToolbarOptions: HtmlToolbarOptions(
    toolbarPosition: ToolbarPosition.belowEditor,
    ),
    ),
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
 * **/