import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../bussiness_logic/product_cubits/product_cubit.dart';
import '../../../../model_layer/home_blocks_model.dart';
import '../../products_screens/product_details_screen.dart';

/// Phase-3 scope: only product links navigate. Category/url links are
/// rendered non-tappable until the category screen exposes a direct route.
void handleBlockLink(BuildContext context, dynamic rawLink) {
  final link = BlockLink.fromJson(rawLink);
  if (link == null) return;
  if (link.type == 'product' && link.value is int) {
    context.read<ProductsCubit>().getSingleProduct(id: link.value as int);
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => const ProductDetailsScreen(isSearch: false),
      ),
    );
  }
}
