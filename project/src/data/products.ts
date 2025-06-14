import { Product } from '../types/product';

export const featuredProducts: Product[] = [
  {
    id: 'p1',
    name: 'Organic Cotton T-Shirt',
    brand: 'EcoWear',
    description: 'Classic t-shirt made from 100% organic cotton. Soft, breathable, and eco-friendly.',
    price: 29.99,
    images: [
      'https://images.pexels.com/photos/5698851/pexels-photo-5698851.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
      'https://images.pexels.com/photos/6612371/pexels-photo-6612371.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'
    ],
    category: 'tops',
    sustainabilityScore: 85,
    sustainabilityDetails: {
      materials: 'Organic cotton, GOTS certified',
      production: 'Fair Trade certified facility',
      packaging: 'Recycled and biodegradable materials',
      carbonFootprint: '2.5kg CO2e (60% less than conventional)'
    },
    sizes: ['XS', 'S', 'M', 'L', 'XL'],
    colors: ['White', 'Black', 'Navy', 'Sage'],
    isNew: true,
    tags: ['organic', 'basics', 'sustainable']
  },
  {
    id: 'p2',
    name: 'Recycled Denim Jeans',
    brand: 'ReNew Denim',
    description: 'Stylish jeans made from 80% recycled denim and 20% organic cotton. Features a classic five-pocket design.',
    price: 89.99,
    images: [
      'https://images.pexels.com/photos/4210863/pexels-photo-4210863.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
      'https://images.pexels.com/photos/7691168/pexels-photo-7691168.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'
    ],
    category: 'bottoms',
    sustainabilityScore: 78,
    sustainabilityDetails: {
      materials: '80% recycled denim, 20% organic cotton',
      production: 'Water-saving production process',
      packaging: 'Plastic-free packaging',
      carbonFootprint: '5.8kg CO2e (40% less than conventional)'
    },
    sizes: ['24', '26', '28', '30', '32', '34'],
    colors: ['Light Wash', 'Medium Wash', 'Dark Wash'],
    isBestseller: true,
    tags: ['recycled', 'denim', 'sustainable']
  },
  {
    id: 'p3',
    name: 'Hemp Blend Blazer',
    brand: 'GreenLine',
    description: 'Sophisticated blazer made from a blend of hemp and organic cotton. Perfect for both casual and formal occasions.',
    price: 149.99,
    salePrice: 119.99,
    images: [
      'https://images.pexels.com/photos/2955375/pexels-photo-2955375.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
      'https://images.pexels.com/photos/6626903/pexels-photo-6626903.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'
    ],
    category: 'outerwear',
    sustainabilityScore: 90,
    sustainabilityDetails: {
      materials: '55% hemp, 45% organic cotton',
      production: 'Small-batch production in worker-owned facility',
      packaging: 'Plastic-free, compostable packaging',
      carbonFootprint: '4.2kg CO2e (70% less than conventional)'
    },
    sizes: ['XS', 'S', 'M', 'L', 'XL'],
    colors: ['Beige', 'Black', 'Olive'],
    tags: ['hemp', 'formal', 'sustainable', 'premium']
  },
  {
    id: 'p4',
    name: 'Bamboo Lyocell Dress',
    brand: 'Bamboo Basics',
    description: 'Elegant midi dress made from bamboo lyocell. Silky soft, breathable, and eco-friendly.',
    price: 79.99,
    images: [
      'https://images.pexels.com/photos/6311392/pexels-photo-6311392.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
      'https://images.pexels.com/photos/6311600/pexels-photo-6311600.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'
    ],
    category: 'dresses',
    sustainabilityScore: 82,
    sustainabilityDetails: {
      materials: '100% bamboo lyocell',
      production: 'Closed-loop production process',
      packaging: 'Recycled cardboard and paper',
      carbonFootprint: '3.1kg CO2e (50% less than conventional)'
    },
    sizes: ['XS', 'S', 'M', 'L', 'XL'],
    colors: ['Navy', 'Forest Green', 'Terracotta'],
    isNew: true,
    tags: ['bamboo', 'elegant', 'sustainable']
  },
  {
    id: 'p5',
    name: 'Recycled Polyester Puffer Jacket',
    brand: 'EcoAlpine',
    description: 'Warm and stylish puffer jacket made from 100% recycled polyester from plastic bottles. Features a water-resistant outer shell.',
    price: 199.99,
    images: [
      'https://images.pexels.com/photos/7691053/pexels-photo-7691053.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
      'https://images.pexels.com/photos/6770028/pexels-photo-6770028.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'
    ],
    category: 'outerwear',
    sustainabilityScore: 75,
    sustainabilityDetails: {
      materials: '100% recycled polyester (from plastic bottles)',
      production: 'PFC-free water repellent treatment',
      packaging: 'Minimal recycled packaging',
      carbonFootprint: '7.5kg CO2e (30% less than conventional)'
    },
    sizes: ['XS', 'S', 'M', 'L', 'XL', 'XXL'],
    colors: ['Black', 'Navy', 'Forest Green'],
    isBestseller: true,
    tags: ['recycled', 'outerwear', 'winter']
  },
  {
    id: 'p6',
    name: 'Handcrafted Cork Handbag',
    brand: 'Cork Collective',
    description: 'Elegant handbag made from sustainable cork. Lightweight, durable, and water-resistant.',
    price: 129.99,
    images: [
      'https://images.pexels.com/photos/1152077/pexels-photo-1152077.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
      'https://images.pexels.com/photos/934673/pexels-photo-934673.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1'
    ],
    category: 'accessories',
    sustainabilityScore: 95,
    sustainabilityDetails: {
      materials: 'Sustainably harvested cork, organic cotton lining',
      production: 'Handcrafted by artisans with fair wages',
      packaging: 'Plastic-free, reusable packaging',
      carbonFootprint: '1.2kg CO2e (85% less than conventional leather)'
    },
    sizes: ['One Size'],
    colors: ['Natural', 'Black', 'Pink'],
    isNew: false,
    tags: ['cork', 'accessories', 'vegan', 'handcrafted']
  }
];