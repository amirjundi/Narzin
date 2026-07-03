part of 'home_blocks_cubit.dart';

@immutable
abstract class HomeBlocksState {}

class HomeBlocksInitial extends HomeBlocksState {}

class HomeBlocksLoading extends HomeBlocksState {}

class HomeBlocksLoaded extends HomeBlocksState {}

class HomeBlocksError extends HomeBlocksState {}
