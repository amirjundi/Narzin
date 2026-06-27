part of 'product_cubit.dart';

@immutable
sealed class ProductsState {}

final class ProductsInitial extends ProductsState {}
final class ProductsSuccess extends ProductsState {}
final class SingleProductSuccess extends ProductsState {}


