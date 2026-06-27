import 'package:dio_cache_interceptor_hive_store/dio_cache_interceptor_hive_store.dart';
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter_map_cache/flutter_map_cache.dart';
import 'package:latlong2/latlong.dart';
import 'package:narzin/bussiness_logic/order_cubits/order_cubit.dart';

class OrdersMapViewer extends StatelessWidget {
  const OrdersMapViewer({super.key});


  @override
  Widget build(BuildContext context) {
    return BlocBuilder<OrderCubit, OrderState>(
      builder: (context, state) {
        return Stack(
          children: [
            FlutterMap(
              mapController: context.read<OrderCubit>().ordersMapController,
              options: MapOptions(
                onPositionChanged: (camera, hasGesture) {
                  // print(camera.zoom);
                },
                initialCenter: LatLng(
                  context.read<OrderCubit>().position?.latitude ?? 0,
                  context.read<OrderCubit>().position?.longitude ?? 0,
                ),
                keepAlive: false,
                onMapReady: () {
                  context.read<OrderCubit>().initializeOrdersMapController();
                },
                onTap: (tapPosition, point) {
                  context.read<OrderCubit>().captureNewPosition(point);
                },
              ),
              children: [
                TileLayer(
                  urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                  userAgentPackageName: 'com.user.narzin',
                  tileProvider: CachedTileProvider(
                    // maxStale keeps the tile cached for the given Duration and
                    // tries to revalidate the next time it gets requested
                    maxStale: const Duration(days: 30),
                    store: HiveCacheStore(
                      context.read<OrderCubit>().path,
                      hiveBoxName: 'HiveCacheStore',
                    ),
                  ),
                ),
                MarkerLayer(markers: [
                  Marker(
                    point: LatLng(
                      context.read<OrderCubit>().position?.latitude ?? 0,
                      context.read<OrderCubit>().position?.longitude ?? 0,
                    ),
                    child: const Icon(
                      Icons.location_on_outlined,
                      color: Colors.red,
                      size: 40,
                    ),
                  ),
                ]),
              ],
            ),
          ],
        );
      },
    );
  }
}
