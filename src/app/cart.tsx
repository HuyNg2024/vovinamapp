import React from 'react';
import { StyleSheet, View, Text, FlatList, Image, TouchableOpacity, SafeAreaView, Platform, Alert } from 'react-native';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import * as WebBrowser from 'expo-web-browser';
import { useCartStore } from '../store/cartStore';
import apiClient from '../api/client';

export default function CartScreen() {
  const router = useRouter();
  const { items, removeFromCart, updateQuantity, getTotalPrice, clearCart } = useCartStore();

  const formatPrice = (price: string | number) => {
    return parseInt(price.toString()).toLocaleString('vi-VN') + ' đ';
  };

  const handleCheckout = async () => {
    if (items.length === 0) {
      Alert.alert('Giỏ hàng trống', 'Vui lòng thêm sản phẩm vào giỏ hàng.');
      return;
    }

    try {
      const amount = getTotalPrice();
      // POST tới Backend VovinamApp (đã tái cấu trúc PayController ở Phase 1)
      // Endpoint /api/pay này sẽ trả về { vnpUrl: "https://sandbox.vnpayment.vn/..." }
      const response = await apiClient.post('/pay', {
        amount: amount,
        orderInfo: 'Thanh toan don hang VovinamApp',
      });

      if (response.data && response.data.vnpUrl) {
        // Mở trình duyệt Webview an toàn trong App
        const result = await WebBrowser.openBrowserAsync(response.data.vnpUrl);
        
        if (result.type === 'cancel' || result.type === 'dismiss') {
          // Khi người dùng tắt trình duyệt (hoặc VNPay redirect lại app)
          Alert.alert('Thông báo', 'Bạn đã đóng cổng thanh toán.');
          // TODO: Tùy biến sâu hơn khi VNPay trả về kết quả qua Deep Linking
        }
      } else {
        Alert.alert('Lỗi', 'Không thể khởi tạo thanh toán VNPay.');
      }
    } catch (error) {
      console.error(error);
      Alert.alert('Lỗi kết nối', 'Không thể kết nối đến máy chủ thanh toán.');
    }
  };

  const renderItem = ({ item }: { item: any }) => (
    <View style={styles.cartItem}>
      <Image source={{ uri: item.img }} style={styles.itemImage} />
      <View style={styles.itemInfo}>
        <Text style={styles.itemName} numberOfLines={2}>{item.ProductName}</Text>
        <Text style={styles.itemPrice}>{formatPrice(item.Price)}</Text>
        
        <View style={styles.quantityContainer}>
          <TouchableOpacity 
            style={styles.qtyBtn} 
            onPress={() => item.quantity > 1 ? updateQuantity(item.ProductID, item.quantity - 1) : removeFromCart(item.ProductID)}
          >
            <Text style={styles.qtyBtnText}>-</Text>
          </TouchableOpacity>
          <Text style={styles.qtyText}>{item.quantity}</Text>
          <TouchableOpacity 
            style={styles.qtyBtn} 
            onPress={() => updateQuantity(item.ProductID, item.quantity + 1)}
          >
            <Text style={styles.qtyBtnText}>+</Text>
          </TouchableOpacity>
        </View>
      </View>
      <TouchableOpacity style={styles.deleteBtn} onPress={() => removeFromCart(item.ProductID)}>
        <Text style={{ fontSize: 20 }}>🗑️</Text>
      </TouchableOpacity>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <LinearGradient colors={['#1a365d', '#2b6cb0']} style={styles.header}>
        <TouchableOpacity style={styles.backBtn} onPress={() => router.back()}>
          <Text style={styles.backBtnText}>{'< Trở lại'}</Text>
        </TouchableOpacity>
        <Text style={styles.headerTitle}>Giỏ Hàng Của Bạn</Text>
        <View style={{ width: 60 }} /> {/* Spacer */}
      </LinearGradient>

      {items.length === 0 ? (
        <View style={styles.emptyContainer}>
          <Text style={{ fontSize: 60, marginBottom: 16 }}>🛒</Text>
          <Text style={styles.emptyText}>Giỏ hàng đang trống.</Text>
          <TouchableOpacity style={styles.shopNowBtn} onPress={() => router.back()}>
            <Text style={styles.shopNowText}>Tiếp tục mua sắm</Text>
          </TouchableOpacity>
        </View>
      ) : (
        <FlatList
          data={items}
          keyExtractor={(item) => item.ProductID.toString()}
          renderItem={renderItem}
          contentContainerStyle={styles.listContainer}
        />
      )}

      {items.length > 0 && (
        <View style={styles.footer}>
          <View style={styles.totalContainer}>
            <Text style={styles.totalLabel}>Tổng thanh toán:</Text>
            <Text style={styles.totalPrice}>{formatPrice(getTotalPrice())}</Text>
          </View>
          <TouchableOpacity style={styles.checkoutBtn} onPress={handleCheckout}>
            <LinearGradient colors={['#e53e3e', '#c53030']} style={styles.checkoutGradient}>
              <Text style={styles.checkoutText}>THANH TOÁN VNPAY</Text>
            </LinearGradient>
          </TouchableOpacity>
        </View>
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f7fafc',
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    padding: 16,
    paddingTop: Platform.OS === 'android' ? 40 : 16,
  },
  backBtn: {
    padding: 8,
  },
  backBtnText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: 'bold',
  },
  headerTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#fff',
  },
  emptyContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  emptyText: {
    fontSize: 18,
    color: '#718096',
    marginBottom: 24,
  },
  shopNowBtn: {
    backgroundColor: '#2b6cb0',
    paddingVertical: 12,
    paddingHorizontal: 24,
    borderRadius: 24,
  },
  shopNowText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 16,
  },
  listContainer: {
    padding: 16,
  },
  cartItem: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    borderRadius: 16,
    padding: 12,
    marginBottom: 16,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 5,
    elevation: 2,
  },
  itemImage: {
    width: 80,
    height: 80,
    borderRadius: 8,
    backgroundColor: '#e2e8f0',
  },
  itemInfo: {
    flex: 1,
    marginLeft: 12,
  },
  itemName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#2d3748',
  },
  itemPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#e53e3e',
    marginTop: 4,
  },
  quantityContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 8,
  },
  qtyBtn: {
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: '#edf2f7',
    justifyContent: 'center',
    alignItems: 'center',
  },
  qtyBtnText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#4a5568',
  },
  qtyText: {
    marginHorizontal: 12,
    fontSize: 16,
    fontWeight: 'bold',
  },
  deleteBtn: {
    padding: 8,
  },
  footer: {
    backgroundColor: '#fff',
    padding: 24,
    borderTopWidth: 1,
    borderTopColor: '#e2e8f0',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: -4 },
    shadowOpacity: 0.05,
    shadowRadius: 10,
    elevation: 10,
  },
  totalContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 16,
  },
  totalLabel: {
    fontSize: 16,
    color: '#4a5568',
  },
  totalPrice: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#e53e3e',
  },
  checkoutBtn: {
    borderRadius: 12,
    overflow: 'hidden',
  },
  checkoutGradient: {
    paddingVertical: 16,
    alignItems: 'center',
  },
  checkoutText: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
    letterSpacing: 1,
  }
});
