<template>
  <div class="main-content">
    <breadcumb :page="$t('Purchase Order Details')" :folder="$t('Purchase Order')"/>
    <div v-if="isLoading" class="loading_page spinner spinner-primary mr-3"></div>

    <b-card no-body v-if="!isLoading">
      <b-card-header>
        <button @click="print_product()" class="btn btn-outline-primary">
          <i class="i-Billing"></i>
          {{$t('print')}}
        </button>
      </b-card-header>
      <b-card-body>
        <b-row id="print_product">
          <b-col md="12" class="mb-5" v-if="product.type != 'is_variant'">
            <barcode
              class="barcode"
              :format="product.Type_barcode"
              :value="product.code"
              textmargin="0"
              fontoptions="bold"
            ></barcode>
          </b-col>

          <b-col md="8">
            <table class="table table-hover table-bordered table-md">
              <tbody>
                 <tr>
                  <td>{{$t('Customer Name')}}</td>
                  <th>{{product.cust_name}}</th>
                </tr>
                <tr>
                  <td>{{$t('PO Number')}}</td>
                  <th>{{product.po_number}}</th>
                </tr>
                <tr>
                  <td>{{$t('Export Number')}}</td>
                  <th>{{product.export_order}}</th>
                </tr>
                <tr>
                  <td>{{$t('Order Date')}}</td>
                  <th>{{product.order_date}}</th>
                </tr>
                <tr>
                  <td>{{$t('Delivery Date')}}</td>
                  <th>{{product.delivery_date}}</th>
                </tr>
                <tr>
                  <td>{{$t('Status')}}</td>
                  <th>{{product.status}}</th>
                </tr>
              </tbody>
            </table>
          </b-col>
          <b-col md="4" class="mb-30">
            <div class="carousel_wrap">
              <b-carousel
                id="carousel-1"
                :interval="2000"
                controls
                background="#ababab"
                img-width="1024"
                img-height="480"
                @sliding-start="onSlideStart"
                @sliding-end="onSlideEnd"
              >
                <b-carousel-slide
                  v-for="(image, index) in product.images"
                  :key="index"
                  :img-src="'/images/products/'+image"
                ></b-carousel-slide>
              </b-carousel>
            </div>
          </b-col>

          <!-- product variant -->
          <b-col md="8" class="mt-4">
            <table class="table table-hover table-sm">
              <thead>
                <tr>
                  <th>{{$t('Dimensions (LxWxH)')}}</th>
                  <th>{{$t('Material')}}</th>
                  <th>{{$t('Ply')}}</th>
                  <th>{{$t('Quantity')}}</th>
                  <th>{{$t('Paper Type')}}</th>
                  <th>{{$t('Unit Price')}}</th>
                  <th>{{$t('Bill of Material')}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="product_variant_data in product.products_variants_data">
                  <td>{{product_variant_data.carton_size}}</td>
                  <td>{{product_variant_data.material}}</td>
                  <td>{{product_variant_data.ply}}</td>
                  <td>{{product_variant_data.quantity}}</td>
                  <td>{{product_variant_data.paper_type}}</td>
                  <td>{{currentUser.currency}} {{product_variant_data.unit_price}}</td>
                  <td><b-button class="small">Generate BOM</b-button></td>
                </tr>
              </tbody>
            </table>
          </b-col>


          <!-- Warehouse Quantity -->
          <b-col md="8" class="mt-4" v-if="product.type == 'is_single'">
            <table class="table table-hover table-sm">
              <thead>
                <tr>
                  <th>{{$t('warehouse')}}</th>
                  <th>{{$t('Quantity')}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="PROD_W in product.CountQTY">
                  <td>{{PROD_W.mag}}</td>
                  <td>{{formatNumber(PROD_W.qte ,2)}} {{product.unit}}</td>
                </tr>
              </tbody>
            </table>
          </b-col>

          <!-- Warehouse Variants Quantity -->
          <b-col md="7" v-if="product.type == 'is_variant'" class="mt-4">
            <table class="table table-hover table-sm">
              <thead>
                <tr>
                  <th>{{$t('warehouse')}}</th>
                  <th>{{$t('Variant')}}</th>
                  <th>{{$t('Quantity')}}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="PROD_V in product.CountQTY_variants">
                  <td>{{PROD_V.mag}}</td>
                  <td>{{PROD_V.variant}}</td>
                  <td>{{formatNumber(PROD_V.qte ,2)}} {{product.unit}}</td>
                </tr>
              </tbody>
            </table>
          </b-col>
        </b-row>
        <hr v-show="product.note">
        <b-row class="mt-4">
           <b-col md="12">
             <p>{{product.note}}</p>
           </b-col>
        </b-row>
      </b-card-body>
    </b-card>
  </div>
</template>


<script>
import VueBarcode from "vue-barcode";
import { mapActions, mapGetters } from "vuex";

export default {
  metaInfo: {
    title: "Detail Product"
  },
  components: {
    barcode: VueBarcode
  },

  data() {
    return {
      len: 8,
      images: [],
      imageArray: [],
      isLoading: true,
      product: {},
      roles: {},
      variants: []
    };
  },
  computed: {
    ...mapGetters(["currentUser"])
  },

  methods: {


    //------------------------------Formetted Numbers -------------------------\\
    formatNumber(number, dec) {
      const value = (typeof number === "string"
        ? number
        : number.toString()
      ).split(".");
      if (dec <= 0) return value[0];
      let formated = value[1] || "";
      if (formated.length > dec)
        return `${value[0]}.${formated.substr(0, dec)}`;
      while (formated.length < dec) formated += "0";
      return `${value[0]}.${formated}`;
    },

     //------- printproduct
    print_product() {
       this.$htmlToPaper('print_product');
    },

    //----------------------------------- Get Details Product ------------------------------\\
    showDetails() {
      let id = this.$route.params.id;
      axios
        .get(`get_po_detail/${id}`)
        .then(response => {
          this.product = response.data;
          this.isLoading = false;
        })
        .catch(response => {
          setTimeout(() => {
            this.isLoading = false;
          }, 500);
        });
    }
  }, //end Methods

  //-----------------------------Created function-------------------

  created: function() {
    this.showDetails();
  }
};
</script>
