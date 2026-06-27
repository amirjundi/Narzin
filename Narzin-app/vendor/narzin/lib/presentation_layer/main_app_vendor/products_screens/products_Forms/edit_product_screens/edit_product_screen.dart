import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_decorated_container/flutter_decorated_container.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_manipulation_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/helpers.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/core/validations.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/model_layer/categories_model.dart';
import 'package:narzin/model_layer/single_produt_model.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/image_widgets/insta_image_widget.dart';
import 'package:narzin/widgets/text_form_fields/custom_input_decorator.dart';
import 'package:narzin/widgets/text_form_fields/custom_text_form_field.dart';

import '../../../../../bussiness_logic/login_cubits/login_cubit.dart';
import 'edit_product_details_addition_screen.dart';

class EditProductScreen extends StatefulWidget {
  const EditProductScreen({super.key, required this.product});

  final SingleProductModel product;
  static final _key = GlobalKey<FormState>();

  @override
  State<EditProductScreen> createState() => _EditProductScreenState();
}

class _EditProductScreenState extends State<EditProductScreen> {
  @override
  void initState() {
    // TODO: implement initState

    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).edit,
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
              key: EditProductScreen._key,
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
                            items: items
                                .map(
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
                  // Column(
                  //   crossAxisAlignment: CrossAxisAlignment.stretch,
                  //   children: [
                  //     Text(S.of(context).product_images_videos),
                  //     const SizedBox(
                  //       height: 10,
                  //     ),
                  //     BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                  //       builder: (context, state) {
                  //         File? image = context.read<ProductManipulationCubit>().image;
                  //         List<File?> images = context.read<ProductManipulationCubit>().images;
                  //         return Column(
                  //           children: [
                  //             InkWell(
                  //               onTap: () async {
                  //                 await context.read<ProductManipulationCubit>().pickImageFromGallery();
                  //               },
                  //               child: DecoratedContainer(
                  //                 strokeWidth: 1,
                  //                 dashSpace: 4,
                  //                 dashWidth: 6,
                  //                 cornerRadius: 16,
                  //                 strokeColor: Colors.grey,
                  //                 child: SizedBox(
                  //                   width: ScreenSizing.width,
                  //                   height: 100,
                  //                   child: image != null
                  //                       ? ClipRRect(
                  //                           borderRadius: BorderRadius.circular(10),
                  //                           child: Image.file(
                  //                             image,
                  //                             fit: BoxFit.cover,
                  //                           ))
                  //                       : Row(
                  //                           mainAxisAlignment: MainAxisAlignment.center,
                  //                           crossAxisAlignment: CrossAxisAlignment.center,
                  //                           children: [
                  //                             const Icon(
                  //                               Icons.perm_media_outlined,
                  //                               size: 20,
                  //                             ),
                  //                             const SizedBox(
                  //                               width: 10,
                  //                             ),
                  //                             Text(S.of(context).product_media),
                  //                           ],
                  //                         ),
                  //                 ),
                  //               ),
                  //             ),
                  //             images.isNotEmpty
                  //                 ? Container(
                  //                     height: 100,
                  //                     margin: const EdgeInsets.symmetric(vertical: 10),
                  //                     padding: const EdgeInsets.symmetric(horizontal: 10),
                  //                     decoration: BoxDecoration(borderRadius: BorderRadius.circular(20), border: Border.all(color: Colors.grey[300]!)),
                  //                     child: ListView.builder(
                  //                       itemBuilder: (context, index) => Stack(
                  //                         children: [
                  //                           Container(
                  //                             width: 80,
                  //                             margin: const EdgeInsets.symmetric(horizontal: 5, vertical: 10),
                  //                             decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: Constants.grey)),
                  //                             child: ClipRRect(
                  //                               borderRadius: BorderRadius.circular(10),
                  //                               child: InstaFileImageWidget(
                  //                                 image: images[index]!,
                  //                               ),
                  //                             ),
                  //                           ),
                  //                           Positioned(
                  //                             top: 3,
                  //                             right: 0,
                  //                             child: IconButton(
                  //                                 onPressed: () {
                  //                                   // context.read<ProductManipulationCubit>().deleteImage(index);
                  //                                 },
                  //                                 icon: const Icon(
                  //                                   Icons.delete,
                  //                                   color: Colors.red,
                  //                                   size: 20,
                  //                                 )),
                  //                           )
                  //                         ],
                  //                       ),
                  //                       scrollDirection: Axis.horizontal,
                  //                       itemCount: images.length,
                  //                     ),
                  //                   )
                  //                 : const SizedBox()
                  //           ],
                  //         );
                  //       },
                  //     ),
                  //   ],
                  // ),
                  const SizedBox(
                    height: 20,
                  ),
                  BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                    builder: (context, state) {
                      var variants = widget.product.data?.variants;
                      var images = widget.product.data?.images;
                      print(images);
                      var imagesToDelete = context.read<ProductManipulationCubit>().imagesToDelete;
                      String locale = BlocProvider.of<LocalizationCubit>(context).locale;
                      return SizedBox(
                        width: ScreenSizing.width,
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Text(
                              S.of(context).product_variants,
                              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w500),
                            ),
                            const SizedBox(
                              height: 10,
                            ),
                            SizedBox(
                              height: 100,
                              width: ScreenSizing.width,
                              child: ListView.builder(
                                scrollDirection: Axis.horizontal,
                                shrinkWrap: true,
                                itemCount: images?.length ?? 0,
                                itemBuilder: (context, index) {
                                  print(images?[index].image);
                                  return imagesToDelete.contains(int.tryParse(images?[index].id??'0'))?Container(): Row(
                                    children: [
                                      Stack(
                                        children: [
                                          ClipRRect(
                                            borderRadius: BorderRadius.circular(10),
                                            child: InstaNetworkImageWidget(
                                              imageUrl: images?[index].image ?? '',
                                            ),
                                          ),
                                          Positioned(
                                            top: 0,
                                            left: 0,
                                            child: IconButton(onPressed: () {
                                              context.read<ProductManipulationCubit>().setImagesToDelete(int.tryParse(images?[index].id.toString()??'0')??0);
                                            }, icon: const Icon(Icons.close)),
                                          )
                                        ],
                                      ),
                                      const SizedBox(
                                        width: 10,
                                      ),
                                    ],
                                  );
                                },
                              ),
                            ),
                            const SizedBox(
                              height: 10,
                            ),
                            ListView.builder(
                              itemBuilder: (context, index) {
                                var variants = widget.product.data?.variants;
                                return context.read<ProductManipulationCubit>().variantsToRemove.contains(int.tryParse(variants?[index].id ?? '') ?? 0)? Container():
                                Card(
                                  color: Colors.grey[100],
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 10),
                                    width: ScreenSizing.width,
                                    child: Column(
                                      children: [
                                        Row(
                                          children: [
                                            const Spacer(),
                                            InkWell(
                                                onTap: () {
                                                  context.read<ProductManipulationCubit>().AddVariantToDelete(int.tryParse(variants?[index].id ?? '') ?? 0);
                                                },
                                                child: const Icon(
                                                  Icons.playlist_remove_outlined,
                                                  color: Colors.red,
                                                ))
                                          ],
                                        ),
                                        ...?(variants?[index].attributes?.map((e) {
                                          // print('xxxxxxxxxxxxxxxxxxxx');
                                          // print(e.value ?? '');

                                          return Row(
                                            children: [
                                              Text(
                                                locale == 'ar' ? (e.nameArabic ?? '') : (e.nameGerman ?? ''),
                                                style: const TextStyle(height: 2, fontWeight: FontWeight.w600),
                                              ),
                                              const Spacer(),
                                              (((int.tryParse(e.attributeId ?? '0') ?? 0) == 1) || (int.tryParse(e.attributeId ?? '0') ?? 0) == 4)
                                                  ? CircleAvatar(
                                                      radius: 15,
                                                      backgroundColor:(int.tryParse(e.attributeId ?? '0') ?? 0) == 4?null: Color(int.tryParse(((e.value?.contains('#')??false)?e.value?.replaceAll('#', '0xff'): e.value) ?? '') ?? (0xff000000)),
                                                      backgroundImage:(int.tryParse(e.attributeId ?? '0') ?? 0) != 4?null: ((e.value ??'').contains('https://')?NetworkImage((e.value ??'')):MemoryImage(Helpers.decodeBase64Image(e.value ??''))),
                                                    )
                                                  : Text(e.value ?? ''),
                                            ],
                                          );
                                        }).toList()),
                                        Row(
                                          children: [
                                            Text(
                                              S.of(context).quantity,
                                              style: const TextStyle(height: 2, fontWeight: FontWeight.w600),
                                            ),
                                            const Spacer(),
                                            Text(variants?[index].stock.toString() ?? ''),
                                          ],
                                        ),
                                      ],
                                    ),
                                  ),
                                );
                              },
                              itemCount: variants?.length ?? 0,
                              shrinkWrap: true,
                              physics: const NeverScrollableScrollPhysics(),
                            )
                          ],
                        ),
                      );
                    },
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
      bottomNavigationBar: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
            builder: (context, state) {
              bool isLoading = context.read<ProductManipulationCubit>().isLoading;
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 0, horizontal: 15),
                child: CustomSignIn_UpOne(
                  color: Constants.lighterSecondaryColor,
                  title: S.of(context).next,
                  customizeChild: isLoading
                      ? const Center(child: CircularProgressIndicator())
                      : Text(
                          S.of(context).next,
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: Constants.mainColor,
                          ),
                        ),
                  ontap: isLoading
                      ? null
                      : () async {
                          if (EditProductScreen._key.currentState!.validate()) {
                            var res = await context.read<ProductManipulationCubit>().getAttributes();
                            context.read<ProductManipulationCubit>().initializeAttributesForm();
                            context.read<ProductManipulationCubit>().variantsToPost.clear();
                            context.read<ProductManipulationCubit>().resetVariantsForm();
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => BlocProvider.value(
                                  value: context.read<ProductManipulationCubit>(),
                                  child: EditProductDetailsScreen(product: widget.product),
                                ),
                              ),
                            );
                          }
                        },
                ),
              );
            },
          ),
          BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
            builder: (context, state) {
              bool isLoading = context.read<ProductManipulationCubit>().isLoading;
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 0, horizontal: 15),
                child: CustomSignIn_UpOne(
                  title: S.of(context).confirm,
                  customizeChild:isLoading?const Center(child: CircularProgressIndicator(),): Text(
                    S.of(context).confirm,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w700,
                      color: Colors.white,
                    ),
                  ),
                  ontap: isLoading? null: () async {
                    var res = await context.read<ProductManipulationCubit>().updateProduct(token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '',productId: widget.product.data?.id??"0");
                    int? productId = context.read<ProductManipulationCubit>().tempProductId;
                    var imagesToDelete = context.read<ProductManipulationCubit>().imagesToDelete;
                    if(productId != null){
                      await context.read<ProductManipulationCubit>().postProductImages(token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '',productId: widget.product.data?.id??"0",);
                    }
                    if(imagesToDelete.isNotEmpty){
                      await context.read<ProductManipulationCubit>().deleteProductImages(token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '',productId: widget.product.data?.id??"0",);
                    }
                    if(res == null){
                      BlocProvider.of<ProductCubit>(context).getVendorProducts(vendor_id: BlocProvider.of<LoginCubit>(context).vendorData?.data?.vendorDetails?.id);
                      Navigator.canPop(context)?Navigator.popUntil(context,(route) => route.isFirst,):null;
                    }
                  },
                ),
              );
            },
          ),
        ],
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
