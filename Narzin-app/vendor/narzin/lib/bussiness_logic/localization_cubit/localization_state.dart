part of 'localization_cubit.dart';

@immutable
sealed class LocalizationState {}

final class LocalizationInitial extends LocalizationState {}
class LocaleChange extends LocalizationState {}
class Memorize extends LocalizationState {}
