import React, { useEffect, useState } from 'react';
import { StyleSheet, View, Text, FlatList, Image, TouchableOpacity, ActivityIndicator, SafeAreaView, Dimensions } from 'react-native';
import { LinearGradient } from 'expo-linear-gradient';
import { useRouter } from 'expo-router';
import apiClient from '../../api/client';
import { useCartStore } from '../../store/cartStore';

const { width } = Dimensions.get('window');
const cardWidth = width / 2 - 24; // 2 columns with padding

interface Product {
  ProductID: number;
  ProductName: string;
  Price: string;
  img: string;
}

export default function StoreScreen() {
  const router = useRouter();
  const [products, setProducts] = useState<Product[]>([]);
  const [loading, setLoading] = useState(true);
  
  const addToCart = useCartStore((state) => state.addToCart);
  const cartItems = useCartStore((state) => state.items);
  const cartCount = cartItems.reduce((acc, item) => acc + item.quantity, 0);

  useEffect(() => {
    fetchProducts();
  }, []);

  const fetchProducts = async () => {
    try {
      setLoading(true);
      // Giả lập API call nếu endpoint chưa sẵn sàng, 
      // Trên thực tế đây là GET /api/products
      const response = await apiClient.get('/products').catch(() => null);
      
      if (response && response.data && response.data.data) {
        setProducts(response.data.data);
      } else {
        // Fallback mock data để demo UI
        setProducts([
          { ProductID: 1, ProductName: 'Võ phục Vovinam Loại 1', Price: '250000', img: 'https://images.unsplash.com/photo-1555597673-b21d5c935865?auto=format&fit=crop&w=300&q=80' },
          { ProductID: 2, ProductName: 'Mộc nhân luyện tập', Price: '3500000', img: 'https://images.unsplash.com/photo-1555597673-b21d5c935865?auto=format&fit=crop&w=300&q=80' },
          { ProductID: 3, ProductName: 'Đai xanh Vovinam', Price: '50000', img: 'https://images.unsplash.com/photo-1555597673-b21d5c935865?auto=format&fit=crop&w=300&q=80' },
          { ProductID: 4, ProductName: 'Đai vàng Vovinam', Price: '70000', img: 'https://images.unsplash.com/photo-1555597673-b21d5c935865?auto=format&fit=crop&w=300&q=80' },
        ]);
      }
    } catch (error) {
      console.error(error);
    } finally {
      setLoading(false);
    }
  };

  const formatPrice = (price: string) => {
    return parseInt(price).toLocaleString('vi-VN') + ' đ';
  };

  const handleAddToCart = (item: Product) => {
    addToCart(item);
    // TODO: Optionally call POST /api/cart/add to sync with backend
  };

  const renderProduct = ({ item }: { item: Product }) => (
    <View style={styles.card}>
      <Image source={{ uri: item.img }} style={styles.image} />
      <View style={styles.cardInfo}>
        <Text style={styles.productName} numberOfLines={2}>{item.ProductName}</Text>
        <Text style={styles.productPrice}>{formatPrice(item.Price)}</Text>
        <TouchableOpacity style={styles.addButton} onPress={() => handleAddToCart(item)}>
          <Text style={styles.addButtonText}>+ Thêm vào giỏ</Text>
        </TouchableOpacity>
      </View>
    </View>
  );

  return (
    <SafeAreaView style={styles.container}>
      <LinearGradient colors={['#1a365d', '#2b6cb0']} style={styles.header}>
        <Text style={styles.headerTitle}>Võ Phục & Dụng Cụ</Text>
        <TouchableOpacity style={styles.cartIcon} onPress={() => router.push('/cart')}>
          <Text style={{ fontSize: 24 }}>🛒</Text>
          {cartCount > 0 && (
            <View style={styles.badge}>
              <Text style={styles.badgeText}>{cartCount}</Text>
            </View>
          )}
        </TouchableOpacity>
      </LinearGradient>

      {loading ? (
        <View style={styles.loadingContainer}>
          <ActivityIndicator size="large" color="#2b6cb0" />
        </View>
      ) : (
        <FlatList
          data={products}
          keyExtractor={(item) => item.ProductID.toString()}
          renderItem={renderProduct}
          numColumns={2}
          contentContainerStyle={styles.gridList}
          showsVerticalScrollIndicator={false}
        />
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
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 24,
    paddingTop: Platform.OS === 'android' ? 40 : 24,
    borderBottomLeftRadius: 20,
    borderBottomRightRadius: 20,
  },
  headerTitle: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#fff',
  },
  cartIcon: {
    position: 'relative',
    padding: 8,
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderRadius: 20,
  },
  badge: {
    position: 'absolute',
    top: -5,
    right: -5,
    backgroundColor: '#e53e3e',
    width: 20,
    height: 20,
    borderRadius: 10,
    justifyContent: 'center',
    alignItems: 'center',
    borderWidth: 2,
    borderColor: '#2b6cb0',
  },
  badgeText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: 'bold',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  gridList: {
    padding: 16,
  },
  card: {
    width: cardWidth,
    backgroundColor: '#fff',
    borderRadius: 16,
    margin: 8,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 8,
    elevation: 3,
    overflow: 'hidden',
  },
  image: {
    width: '100%',
    height: 140,
    backgroundColor: '#e2e8f0',
  },
  cardInfo: {
    padding: 12,
  },
  productName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#2d3748',
    height: 40, // fix height for 2 lines
  },
  productPrice: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#e53e3e', // Red emphasis
    marginTop: 8,
    marginBottom: 12,
  },
  addButton: {
    backgroundColor: '#e53e3e', // Red Button
    paddingVertical: 8,
    borderRadius: 8,
    alignItems: 'center',
  },
  addButtonText: {
    color: '#fff',
    fontWeight: 'bold',
    fontSize: 12,
  }
});
