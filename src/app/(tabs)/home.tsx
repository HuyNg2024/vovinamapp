import React, { useEffect, useState } from 'react';
import { StyleSheet, View, Text, ScrollView, TouchableOpacity, ActivityIndicator, Image, FlatList, SafeAreaView, Dimensions } from 'react-native';
import { useRouter } from 'expo-router';
import { LinearGradient } from 'expo-linear-gradient';
import AsyncStorage from '@react-native-async-storage/async-storage';
import apiClient from '../api/client';

const { width } = Dimensions.get('window');

// Mocks interfaces for TS (optional but good practice)
interface Club {
  id: number;
  ten: string;
  diachi: string;
  img: string;
}

interface VvnClass {
  id: number;
  ten: string;
  thoigian: string;
  ten_club: string;
}

export default function HomeScreen() {
  const router = useRouter();
  const [loading, setLoading] = useState(true);
  const [clubs, setClubs] = useState<Club[]>([]);
  const [classes, setClasses] = useState<VvnClass[]>([]);
  const [userName, setUserName] = useState('Đồng Môn');

  useEffect(() => {
    fetchData();
  }, []);

  const fetchData = async () => {
    try {
      setLoading(true);
      // Fetch user info
      const userRes = await apiClient.post('/me');
      if (userRes.data?.data?.ten) {
        setUserName(userRes.data.data.ten);
      }

      // Fetch clubs
      const clubsRes = await apiClient.get('/clubs');
      if (clubsRes.data?.data) {
        setClubs(clubsRes.data.data);
      }

      // Fetch classes
      const classesRes = await apiClient.get('/classes');
      if (classesRes.data?.data) {
        setClasses(classesRes.data.data);
      }
    } catch (error) {
      console.error('Error fetching home data', error);
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = async () => {
    await AsyncStorage.removeItem('@auth_token');
    router.replace('/');
  };

  if (loading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#2b6cb0" />
        <Text style={{ marginTop: 10, color: '#4a5568' }}>Đang tải võ đường...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <ScrollView showsVerticalScrollIndicator={false}>
        {/* Header Section */}
        <LinearGradient colors={['#1a365d', '#2b6cb0']} style={styles.header}>
          <View style={styles.headerTop}>
            <View>
              <Text style={styles.greeting}>Chào buổi sáng,</Text>
              <Text style={styles.userName}>{userName}!</Text>
            </View>
            <TouchableOpacity onPress={handleLogout} style={styles.logoutBtn}>
              <Text style={styles.logoutText}>Thoát</Text>
            </TouchableOpacity>
          </View>
        </LinearGradient>

        {/* Clubs Section (Horizontal Scroll) */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Các Câu Lạc Bộ</Text>
          <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.clubsScroll}>
            {clubs.length > 0 ? (
              clubs.map((club) => (
                <TouchableOpacity key={club.id} style={styles.clubCard}>
                  {/* Dùng ảnh giả lập nếu API trả về null */}
                  <Image 
                    source={{ uri: club.img || 'https://images.unsplash.com/photo-1555597673-b21d5c935865?q=80&w=300&auto=format&fit=crop' }} 
                    style={styles.clubImage} 
                  />
                  <LinearGradient colors={['transparent', 'rgba(0,0,0,0.8)']} style={styles.clubGradient}>
                    <Text style={styles.clubName} numberOfLines={1}>{club.ten}</Text>
                    <Text style={styles.clubAddress} numberOfLines={1}>{club.diachi || 'Chưa cập nhật địa chỉ'}</Text>
                  </LinearGradient>
                </TouchableOpacity>
              ))
            ) : (
              <Text style={styles.emptyText}>Chưa có câu lạc bộ nào.</Text>
            )}
          </ScrollView>
        </View>

        {/* Classes Section (Vertical List) */}
        <View style={styles.section}>
          <Text style={styles.sectionTitle}>Các Lớp Học Sắp Khai Giảng</Text>
          <View style={styles.classesContainer}>
            {classes.length > 0 ? (
              classes.map((cls) => (
                <TouchableOpacity key={cls.id} style={styles.classCard}>
                  <View style={styles.classIcon}>
                    <Text style={{fontSize: 24}}>🥋</Text>
                  </View>
                  <View style={styles.classInfo}>
                    <Text style={styles.className}>{cls.ten}</Text>
                    <Text style={styles.classClub}>{cls.ten_club}</Text>
                    <Text style={styles.classTime}>{cls.thoigian || 'Lịch học linh hoạt'}</Text>
                  </View>
                </TouchableOpacity>
              ))
            ) : (
              <Text style={styles.emptyText}>Chưa có lớp học nào.</Text>
            )}
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f7fafc',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  header: {
    padding: 24,
    paddingTop: Platform.OS === 'android' ? 40 : 24,
    borderBottomLeftRadius: 30,
    borderBottomRightRadius: 30,
    marginBottom: 20,
    shadowColor: '#2b6cb0',
    shadowOffset: { width: 0, height: 10 },
    shadowOpacity: 0.3,
    shadowRadius: 15,
    elevation: 10,
  },
  headerTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  greeting: {
    color: '#e2e8f0',
    fontSize: 16,
  },
  userName: {
    color: '#fff',
    fontSize: 24,
    fontWeight: 'bold',
    marginTop: 4,
  },
  logoutBtn: {
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 20,
  },
  logoutText: {
    color: '#fff',
    fontWeight: 'bold',
  },
  section: {
    marginBottom: 24,
  },
  sectionTitle: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#2d3748',
    marginLeft: 24,
    marginBottom: 16,
  },
  clubsScroll: {
    paddingLeft: 24,
    paddingRight: 8,
  },
  clubCard: {
    width: width * 0.65,
    height: 180,
    marginRight: 16,
    borderRadius: 20,
    overflow: 'hidden',
    backgroundColor: '#fff',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 10,
    elevation: 5,
  },
  clubImage: {
    width: '100%',
    height: '100%',
  },
  clubGradient: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    height: 80,
    justifyContent: 'flex-end',
    padding: 16,
  },
  clubName: {
    color: '#fff',
    fontSize: 18,
    fontWeight: 'bold',
  },
  clubAddress: {
    color: '#e2e8f0',
    fontSize: 12,
    marginTop: 4,
  },
  classesContainer: {
    paddingHorizontal: 24,
  },
  classCard: {
    flexDirection: 'row',
    backgroundColor: '#fff',
    borderRadius: 16,
    padding: 16,
    marginBottom: 12,
    alignItems: 'center',
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 5,
    elevation: 2,
  },
  classIcon: {
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: '#ebf8ff',
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  classInfo: {
    flex: 1,
  },
  className: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#2d3748',
  },
  classClub: {
    fontSize: 14,
    color: '#4a5568',
    marginTop: 2,
  },
  classTime: {
    fontSize: 12,
    color: '#718096',
    marginTop: 4,
  },
  emptyText: {
    color: '#a0aec0',
    marginLeft: 24,
    fontStyle: 'italic',
  }
});
