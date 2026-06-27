import 'dart:io';

import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_decorated_container/flutter_decorated_container.dart';
import 'package:narzin/bussiness_logic/login_cubits/login_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_cubit.dart';
import 'package:narzin/bussiness_logic/product_manipulation_cubits/product_manipulation_cubit.dart';
import 'package:narzin/core/constants.dart';
import 'package:narzin/core/screen_sizing_constants.dart';
import 'package:narzin/generated/l10n.dart';
import 'package:narzin/model_layer/single_produt_model.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';
import 'package:narzin/widgets/image_widgets/insta_image_widget.dart';

import '../../../../../core/helpers.dart';

class ImageVariantUpdateScreen extends StatelessWidget {
  const ImageVariantUpdateScreen({super.key, required this.product});

  final SingleProductModel product;

  @override
  Widget build(BuildContext context) {
    return WillPopScope(
      onWillPop: () {
        BlocProvider.of<ProductManipulationCubit>(context).setSelectedVariantIndex(-1);
        return Future.value(true);
      },
      child: Scaffold(
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
              BlocProvider.of<ProductManipulationCubit>(context).setSelectedVariantIndex(-1);
              BlocProvider.of<ProductManipulationCubit>(context).colorImages.clear();
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
        /////////////////////////////////////////////////////////////////////////////////////////////
        body: Container(
          height: ScreenSizing.height,
          width: ScreenSizing.width,
          padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 5),
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    BlocProvider.of<ProductManipulationCubit>(context).variantsToPost.isNotEmpty
                        ? Text(
                            S.of(context).variantsToPost,
                          )
                        : SizedBox(),
                    BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                      builder: (context, state) {
                        var variantsToPost = context.read<ProductManipulationCubit>().variantsToPost;
                        var variantProduct = product.data?.variants;
                        if (variantsToPost.isEmpty) {
                          return const SizedBox();
                        }
                        return Container(
                          height: 50,
                          margin: const EdgeInsets.symmetric(vertical: 10),
                          width: ScreenSizing.width,
                          child: FractionallySizedBox(
                            widthFactor: 1.09,
                            child: Row(
                              children: [
                                Expanded(
                                  child: ListView.separated(
                                    scrollDirection: Axis.horizontal,
                                    itemBuilder: (context, index) {
                                      return InkWell(
                                        onTap: () {
                                          context.read<ProductManipulationCubit>().setSelectedVariantIndex(index);
                                          // context.read<ProductManipulationCubit>().reGenerateAttributes(variantsToPost[index]);
                                        },
                                        child: Container(
                                          height: 20,
                                          padding: const EdgeInsets.symmetric(horizontal: 10),
                                          constraints: const BoxConstraints(minWidth: 100),
                                          decoration: BoxDecoration(
                                            borderRadius: BorderRadius.circular(20),
                                            border: context.read<ProductManipulationCubit>().selectedVariantIndex == index ? Border.all(color: Color(int.tryParse(variantsToPost[index].attributes?.firstWhere((e) => e.attributeId == 1).value.toString() ?? '') ?? 0), width: 2) : null,
                                            color: Color(0xfffff811 ~/ (10 + index)),
                                          ),
                                          margin: const EdgeInsets.symmetric(vertical: 5),
                                          child: Center(
                                            child: Row(
                                              children: [
                                                CircleAvatar(
                                                  radius: 15,
                                                  backgroundColor: Color(int.tryParse(variantsToPost[index].attributes?.firstWhere((e) => e.attributeId == 1).value.toString() ?? '') ?? 0),
                                                ),
                                                SizedBox(
                                                  width: 10,
                                                ),
                                                Text('Variant ${index + 1}'),
                                              ],
                                            ),
                                          ),
                                        ),
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
                          ),
                        );
                      },
                    ),
                    product.data?.variants?.isNotEmpty ?? false
                        ? Text(
                            S.of(context).existedVariants,
                          )
                        : SizedBox(),
                    BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                      builder: (context, state) {
                        var variantsToPost = context.read<ProductManipulationCubit>().variantsToPost;
                        var variantProduct = product.data?.variants;

                        if (variantProduct?.isEmpty ?? true) {
                          return const SizedBox();
                        }
                        return Container(
                          height: 50,
                          margin: const EdgeInsets.symmetric(vertical: 10),
                          width: ScreenSizing.width,
                          child: FractionallySizedBox(
                            widthFactor: 1.09,
                            child: Row(
                              children: [
                                Expanded(
                                  child: ListView.separated(
                                    scrollDirection: Axis.horizontal,
                                    itemBuilder: (context, index) {
                                      return InkWell(
                                        onTap: () {
                                          context.read<ProductManipulationCubit>().setSelectedExistedVariantIndex(index);
                                          // context.read<ProductManipulationCubit>().reGenerateAttributes(variantsToPost[index]);
                                        },
                                        child: Container(
                                          height: 20,
                                          padding: const EdgeInsets.symmetric(horizontal: 10),
                                          constraints: const BoxConstraints(minWidth: 100),
                                          decoration: BoxDecoration(
                                            borderRadius: BorderRadius.circular(20),
                                            border: context.read<ProductManipulationCubit>().selectedExistedVariantIndex == index ? Border.all(color: Color(int.tryParse(variantProduct?[index].attributes?.firstWhere((e) => e.attributeId == '1').value.toString() ?? '') ?? 0), width: 2) : null,
                                            color: Color(0xfffff811 ~/ (5 + index)),
                                          ),
                                          margin: const EdgeInsets.symmetric(vertical: 5),
                                          child: Center(
                                            child: Row(
                                              children: [
                                                CircleAvatar(
                                                  radius: 15,
                                                  backgroundColor: Color(int.tryParse(variantProduct?[index].attributes?.firstWhere((e) => e.attributeId == '1').value.toString() ?? '') ?? 0),
                                                  backgroundImage:variantProduct?[index].attributes?.any((e) => e.attributeId == '4')??false? MemoryImage(Helpers.decodeBase64Image(variantProduct?[index].attributes?.firstWhere((e) => e.attributeId == '4').value ?? '')):null,
                                                ),
                                                SizedBox(
                                                  width: 10,
                                                ),
                                                Text('Variant ${index + 1}'),
                                              ],
                                            ),
                                          ),
                                        ),
                                      );
                                    },
                                    separatorBuilder: (context, index) => const SizedBox(
                                      width: 10,
                                    ),
                                    itemCount: variantProduct?.length ?? 0,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                    const SizedBox(
                      height: 10,
                    ),
                    Text(S.of(context).product_images_videos),
                    const SizedBox(
                      height: 10,
                    ),
                    BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                      builder: (context, state) {
                        File? image = context.read<ProductManipulationCubit>().image;
                        Map<String, String> colorImages = context.read<ProductManipulationCubit>().colorImages;
                        return Column(
                          children: [
                            InkWell(
                              onTap: () async {
                                var selectedVariantIndex = context.read<ProductManipulationCubit>().selectedVariantIndex;
                                var selectedExistedVariantIndex = context.read<ProductManipulationCubit>().selectedExistedVariantIndex;
                                if (selectedVariantIndex == -1 && selectedExistedVariantIndex == -1) {
                                  return;
                                }
                                await context.read<ProductManipulationCubit>().pickImageFromGallery();
                              },
                              child: DecoratedContainer(
                                strokeWidth: 1,
                                dashSpace: 4,
                                dashWidth: 6,
                                cornerRadius: 16,
                                strokeColor: Colors.grey,
                                child: Container(
                                  width: ScreenSizing.width,
                                  height: 100,
                                  child: image != null
                                      ? ClipRRect(
                                          borderRadius: BorderRadius.circular(10),
                                          child: Image.file(
                                            image,
                                            fit: BoxFit.cover,
                                          ))
                                      : Row(
                                          mainAxisAlignment: MainAxisAlignment.center,
                                          crossAxisAlignment: CrossAxisAlignment.center,
                                          children: [
                                            const Icon(
                                              Icons.perm_media_outlined,
                                              size: 20,
                                            ),
                                            const SizedBox(
                                              width: 10,
                                            ),
                                            Text(S.of(context).product_media),
                                          ],
                                        ),
                                ),
                              ),
                            ),
                            colorImages.isNotEmpty
                                ? Container(
                                    height: 100,
                                    margin: const EdgeInsets.symmetric(vertical: 10),
                                    padding: const EdgeInsets.symmetric(horizontal: 10),
                                    decoration: BoxDecoration(borderRadius: BorderRadius.circular(20), border: Border.all(color: Colors.grey[300]!)),
                                    child: ListView.builder(
                                      itemBuilder: (context, index) => Stack(
                                        children: [
                                          Container(
                                            width: 80,
                                            margin: const EdgeInsets.symmetric(horizontal: 5, vertical: 10),
                                            decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), border: Border.all(color: Constants.grey)),
                                            child: ClipRRect(
                                              borderRadius: BorderRadius.circular(10),
                                              child: InstaFileImageWidget(
                                                image: File(colorImages.entries.elementAt(index).value),
                                              ),
                                            ),
                                          ),
                                          Positioned(
                                            top: 3,
                                            right: 0,
                                            child: IconButton(
                                                onPressed: () {
                                                  var key = colorImages.entries.elementAt(index).key;
                                                  context.read<ProductManipulationCubit>().deleteImage(key);
                                                },
                                                icon: const Icon(
                                                  Icons.delete,
                                                  color: Colors.red,
                                                  size: 20,
                                                )),
                                          )
                                        ],
                                      ),
                                      scrollDirection: Axis.horizontal,
                                      itemCount: colorImages.entries.length,
                                    ),
                                  )
                                : const SizedBox()
                          ],
                        );
                      },
                    ),
                  ],
                ),
                const SizedBox(
                  height: 20,
                ),
                BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
                  builder: (context, state) {
                    return InkWell(
                      onTap: () {
                        var image = context.read<ProductManipulationCubit>().image;
                        var index = context.read<ProductManipulationCubit>().selectedVariantIndex;
                        var existedIndex = context.read<ProductManipulationCubit>().selectedExistedVariantIndex;
                        if ((index == -1 && existedIndex == -1) || image == null) {
                          return;
                        }
                        if (index != -1 && existedIndex == -1) {
                          var variantsToPost = context.read<ProductManipulationCubit>().variantsToPost;

                          context.read<ProductManipulationCubit>().setColorsImages(variantsToPost[index].attributes?.firstWhere((e) => e.attributeId == 1).value.toString() ?? '', image.path ?? '');
                        }
                        if (index == -1 && existedIndex != -1) {
                          var existedvariants = product.data?.variants;

                          context.read<ProductManipulationCubit>().setColorsImages(existedvariants?[existedIndex].attributes?.firstWhere((e) => e.attributeId == '1').value.toString() ?? '', image.path ?? '');
                        }

                        context.read<ProductManipulationCubit>().image = null;
                        context.read<ProductManipulationCubit>().setSelectedVariantIndex(-1);
                        context.read<ProductManipulationCubit>().setSelectedExistedVariantIndex(-1);
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
              ],
            ),
          ),
        ),
        ///////////////////////////////////////////////////////////////////////////////////////////
        bottomNavigationBar: Padding(
          padding: const EdgeInsets.symmetric(vertical: 0, horizontal: 15),
          child: BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
            builder: (context, state) {
              bool isLoading = context.read<ProductManipulationCubit>().isLoading;
              return CustomSignIn_UpOne(
                title: S.of(context).next,
                customizeChild: isLoading
                    ? const Center(
                        child: CircularProgressIndicator(),
                      )
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
                        var res = await context.read<ProductManipulationCubit>().updateProduct(token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '', productId: product.data?.id ?? "0");
                        int? productId = context.read<ProductManipulationCubit>().tempProductId;
                        var imagesToDelete = context.read<ProductManipulationCubit>().imagesToDelete;
                        if (productId != null) {
                          await context.read<ProductManipulationCubit>().postProductImages(
                                token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '',
                                productId: product.data?.id ?? "0",
                              );
                        }
                        if (imagesToDelete.isNotEmpty) {
                          await context.read<ProductManipulationCubit>().deleteProductImages(
                                token: BlocProvider.of<LoginCubit>(context).vendorData?.data?.token ?? '',
                                productId: product.data?.id ?? "0",
                              );
                        }
                        if (res == null) {
                          context.read<ProductManipulationCubit>().colorImages.clear();
                          BlocProvider.of<ProductCubit>(context).getVendorProducts(vendor_id: BlocProvider.of<LoginCubit>(context).vendorData?.data?.vendorDetails?.id);
                          Navigator.canPop(context)
                              ? Navigator.popUntil(
                                  context,
                                  (route) => route.isFirst,
                                )
                              : null;
                        }
                      },
              );
            },
          ),
        ),
      ),
    );
  }
}
