import 'package:another_xlider/another_xlider.dart';
import 'package:another_xlider/models/handler.dart';
import 'package:another_xlider/models/trackbar.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:narzin/bussiness_logic/localization_cubit/localization_cubit.dart';
import 'package:narzin/model_layer/search_products_model.dart';
import 'package:narzin/widgets/buttons/custom_main_buttons.dart';

import '../../../../bussiness_logic/product_cubits/search_cubit.dart';
import '../../../../core/screen_sizing_constants.dart';
import '../../../../generated/l10n.dart';

class SearchFilters extends StatelessWidget {
  const SearchFilters({super.key});

  Widget _buildCategoriesSection(List<Categories>? categories, String? selectedCategory, String? selectedSubCategory, {required void Function(String?)? onChanged, required void Function(String?)? onSubCatChanged, required BuildContext context}) {
    if (categories == null || categories.isEmpty) {
      return Container();
    }
    String locale = BlocProvider.of<LocalizationCubit>(context).locale;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        ...categories.map(
          (e) => Column(
            children: [
              RadioListTile<String?>(
                contentPadding: EdgeInsets.zero,
                value: e.id.toString(),
                groupValue: selectedCategory,
                onChanged: onChanged,
                title: Text(locale == 'ar' ? (e.nameArabic ?? '') : (e.nameGerman ?? '')),
                // subtitle: ,
              ),
              ((e.subcategories?.isNotEmpty ?? false) && selectedCategory == e.id)
                  ? Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 40.0),
                      child: Column(
                        children: [
                          ...e.subcategories!
                              .map(
                                (e) => RadioListTile(
                                  title: Text(locale == 'ar' ? (e.nameArabic ?? '') : (e.nameGerman ?? '')),
                                  contentPadding: EdgeInsets.zero,
                                  value: e.id.toString(),
                                  groupValue: selectedSubCategory,
                                  onChanged: onSubCatChanged,
                                ),
                              )
                              
                        ],
                      ),
                    )
                  : Container()
            ],
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        toolbarHeight: kToolbarHeight * 1.1,
        bottom: PreferredSize(preferredSize: Size(ScreenSizing.width, 0.1), child: const Divider()),
        backgroundColor: Colors.white,
        title: Text(
          S.of(context).filter,
          style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
        ),
        automaticallyImplyLeading: false,
        actions: [
          IconButton(
            onPressed: () {
              Navigator.canPop(context) ? Navigator.pop(context) : null;
            },
            icon: const Icon(Icons.close),
          ),
        ],
        centerTitle: true,
      ),
      body: BlocBuilder<SearchCubit, SearchState>(
        builder: (context, state) {
          return Container(
            height: ScreenSizing.height,
            width: ScreenSizing.width,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  SizedBox(
                    width: ScreenSizing.width,
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        Text(
                          S.of(context).categories,
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                        ),
                        _buildCategoriesSection(context.read<SearchCubit>().products?.data?.filters?.categories, context.read<SearchCubit>().selectedCategory, context.read<SearchCubit>().childCategoryId, onChanged: (value) {
                          context.read<SearchCubit>().setSelectedCategory(value ?? '');
                        }, onSubCatChanged: (value) {
                          context.read<SearchCubit>().setSelectedSubCategory(value ?? '');
                        }, context: context),
                        const SizedBox(
                          height: 10,
                        ),
                        Divider(
                          color: Colors.grey[300],
                        ),
                        const SizedBox(
                          height: 10,
                        ),
                        Text(
                          S.of(context).price,
                          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                        ),
                        _buildFlutterSlider(context.read<SearchCubit>().products?.data?.filters?.priceRange, context: context),
                        const SizedBox(
                          height: 10,
                        ),
                        Divider(
                          color: Colors.grey[300],
                        ),
                        const SizedBox(
                          height: 10,
                        ),
                      ],
                    ),
                  )
                ],
              ),
            ),
          );
        },
      ),
      bottomNavigationBar: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            Expanded(
              child: CustomSignIn_UpOne(
                title: S.of(context).initialize,
                ontap: () {
                  BlocProvider.of<SearchCubit>(context).prepareFilteredSearchUrl();
                  BlocProvider.of<SearchCubit>(context).getSearchedProducts();
                  Navigator.pop(context);
                },
              ),
            ),
            const SizedBox(
              width: 10,
            ),
            Expanded(
              child: CustomSignIn_UpTwo(
                title: S.of(context).reset,
                ontap: () {
                  BlocProvider.of<SearchCubit>(context).resetFilters();
                  BlocProvider.of<SearchCubit>(context).getSearchedProducts();
                  Navigator.pop(context);
                },
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFlutterSlider(PriceRange? priceRange, {required BuildContext context}) {
    if (priceRange == null) {
      return Container();
    }
    return FlutterSlider(
      trackBar: const FlutterSliderTrackBar(
        activeTrackBar: BoxDecoration(
          gradient: LinearGradient(
            colors: [
              Color(0xff5BB5EF),
              Color(0xff3084C2),
            ],
          ),
        ),
      ),
      handler: FlutterSliderHandler(child: const Material()),
      rightHandler: FlutterSliderHandler(child: const Material()),
      values: [
        BlocProvider.of<SearchCubit>(context).min == 0 ? double.tryParse(priceRange.min ?? '0') ?? 0 : BlocProvider.of<SearchCubit>(context).min,
        BlocProvider.of<SearchCubit>(context).max == 0 ? double.tryParse(priceRange.max ?? '0') ?? 0 : BlocProvider.of<SearchCubit>(context).max,
      ],
      rangeSlider: true,
      max: double.tryParse(priceRange.max ?? '0'),
      min: double.tryParse(priceRange.min ?? '0'),
      onDragging: (handlerIndex, lowerValue, upperValue) {
        BlocProvider.of<SearchCubit>(context).setMinMaxPrice(lowerValue, upperValue);
      },
    );
  }
}
