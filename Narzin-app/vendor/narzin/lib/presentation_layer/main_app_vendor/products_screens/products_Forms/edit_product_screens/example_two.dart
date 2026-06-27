import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';

import '../../../../../bussiness_logic/product_manipulation_cubits/product_manipulation_cubit.dart';
import '../../../../../generated/l10n.dart';

class UpdateProductVariantsScreen extends StatelessWidget {
  const UpdateProductVariantsScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(S.of(context).details),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: BlocBuilder<ProductManipulationCubit, ProductManipulationState>(
        builder: (context, state) {
          return ListView(
            padding: const EdgeInsets.all(16.0),
            children: [
              ...context.read<ProductManipulationCubit>().variantsToPost.map((variant) {
                return ListTile(
                  title: Text('Price: ${variant.price}, Stock: ${variant.stock}'),
                  trailing: IconButton(
                    icon: const Icon(Icons.delete, color: Colors.red),
                    onPressed: () => context.read<ProductManipulationCubit>().variantsToPost.remove(variant),
                  ),
                );
              }),
              ElevatedButton(
                onPressed: () {
                  // Logic to add new variant
                },
                child: Text(S.of(context).add_new_attribute),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () {
                  // Logic to submit update
                },
                child: Text(S.of(context).confirm),
              ),
            ],
          );
        },
      ),
    );
  }
}
