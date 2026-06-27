part of 'product_manipulation_cubit.dart';

@immutable
sealed class ProductManipulationState {}

final class ProductManipulationInitial extends ProductManipulationState {}

class ProductImagePickedSuccess extends ProductManipulationState {
  final File imageFile;

  ProductImagePickedSuccess(this.imageFile);
}

class ProductImageError extends ProductManipulationState {
  final String message;

  ProductImageError(this.message);
}
