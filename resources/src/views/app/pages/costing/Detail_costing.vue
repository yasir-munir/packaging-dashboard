<template>
  <div class="main-content">
    <breadcumb :page="$t('Costing Details')" :folder="$t('Costings')"/>
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
          <!-- <b-col md="12" class="mb-5" v-if="product.type != 'is_variant'">
            <barcode
              class="barcode"
              :format="product.Type_barcode"
              :value="product.code"
              textmargin="0"
              fontoptions="bold"
            ></barcode>
          </b-col> -->

          <b-col md="8">
            <b-row>
                <b-col md="12">
                    <table class="table table-hover table-bordered table-md">
                    <tbody>
                        <tr>
                        <td>{{$t('Customer Name')}}</td>
                        <th>{{product.cust_name}}</th>
                        </tr>
                        <tr>
                        <td>{{$t('Box Size')}}</td>
                        <th>{{product.box_size}}</th>
                        </tr>
                        <tr>
                        <td>{{$t('Box Type')}}</td>
                        <th>{{product.box_type_name}}</th>
                        </tr>
                        <tr>
                        <td>{{$t('Number of Ply')}}</td>
                        <th>{{product.ply}}</th>
                        </tr>
                        <tr>
                        <td>{{$t('Date')}}</td>
                        <th>{{product.order_date}}</th>
                        </tr>
                        <tr>
                        <td>{{$t('Quantity')}}</td>
                        <th>{{product.quantity}}</th>
                        </tr>
                        <tr>
                            <td>{{$t('Measurement')}}</td>
                            <th>{{product.measurement}}</th>
                        </tr>
                        <tr>
                            <td>{{$t('Shade - Paper Type')}}</td>
                            <th>{{product.shade_name}} - {{product.paper_type_name}}</th>
                        </tr>
                        <tr>
                            <td>{{$t('Status')}}</td>
                            <th>{{product.is_active}}</th>
                        </tr>
                    </tbody>
                    </table>
                </b-col>
            </b-row>
            <b-row>
                <b-col md="12" class="mt-4">
                    <table class="table table-hover table-sm">
                      <thead>
                        <tr>
                          <th>{{$t('Ply No.')}}</th>
                          <th>{{$t('Layer type')}}</th>
                          <th>{{$t('Paper')}}</th>
                          <th>{{$t('BF')}}</th>
                          <th>{{$t('Rate')}}</th>
                          <th>{{$t('Grams')}}</th>
                          <th>{{$t('Flute Factor')}}</th>
                          <th>{{$t('Weight')}}</th>
                          <th>{{$t('Approx')}}</th>
                          <th>{{$t('Cost')}}</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="product_variant_data in product.costing_details">
                          <td>{{product_variant_data.ply_no}}</td>
                          <td>{{product_variant_data.paper_type}}</td>
                          <td>{{product_variant_data.paper_name}}</td>
                          <td>{{product_variant_data.paper_bf}}</td>
                          <td>{{currentUser.currency}} {{product_variant_data.paper_rate}}</td>
                          <td>{{product_variant_data.paper_grams}}</td>
                          <td>{{product_variant_data.paper_flute_factor}}</td>
                          <td>{{product_variant_data.paper_weight}}</td>
                          <td>{{product_variant_data.paper_approx}}</td>
                          <td>{{currentUser.currency}} {{product_variant_data.paper_cost}}</td>
                        </tr>
                      </tbody>
                    </table>
                </b-col>
            </b-row>
          </b-col>

          <!-- product variant -->


          <b-col md="4">
            <b-card class="mt-1">
                <b-row>
                    <div class="col-md-12 mt-4">
                        <table class="table table-striped table-sm">
                        <tbody>
                            <tr>
                                <td colspan=3 class="font-weight-bold">
                                    {{ $t("Paper Weight Calculation") }}
                                </td>
                            </tr>
                            <tr>
                                <td class="bold">{{ $t("Craft") }}</td>
                                <td><span>{{ product.total_craft.toFixed(2) }}</span></td>
                                <td class="font-weight-bold">{{ product.total_craft_q.toFixed(2) }}</td>
                            </tr>
                            <tr>
                                <td class="bold">{{ $t("Folding Nali") }}</td>
                                <td><span>{{ product.total_folding_nali.toFixed(2) }}</span></td>
                                <td class="font-weight-bold">{{ product.total_folding_nali_q.toFixed(2) }}</td>
                            </tr>
                            <tr>
                                <td class="bold">{{ $t("Folding") }}</td>
                                <td><span>{{ product.total_folding.toFixed(2) }}</span></td>
                                <td class="font-weight-bold">{{ product.total_folding_q.toFixed(2) }}</td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                </b-row>
                <b-row>
                    <div class="col-md-12 mt-4">
                        <table class="table table-striped table-sm">
                            <tbody>
                                <tr>
                                    <td colspan=2 class="font-weight-bold">
                                        {{ $t("Box Specification and Cost") }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Total Grams") }}</td>
                                    <td class="text-right"><span>{{ product.total_grams.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Total BS") }}</td>
                                    <td class="text-right"><span>{{ product.total_bs.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Total Weight") }}</td>
                                    <td class="text-right"><span>{{ product.total_weight.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Carrogation Cost") }}</td>
                                    <td class="text-right"><span>{{ product.carrogation_cost.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Waste") }}</td>
                                    <td class="text-right"><span>{{ product.waste.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Raw Material Cost") }}</td>
                                    <td class="text-right"><span>{{ product.total_cost.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Conversion Rs/KG") }}</td>
                                    <td class="text-right"><span>{{ product.conversion_per_kg.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Printing") }}</td>
                                    <td class="text-right"><span>{{ product.printing.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Lamination") }}</td>
                                    <td class="text-right"><span>{{ product.lamination.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Profit %") }}</td>
                                    <td class="text-right"><span>{{ product.profit.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td class="bold">{{ $t("Transport") }}</td>
                                    <td class="text-right"><span>{{ product.transport.toFixed(2) }}</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="font-weight-bold">{{
                                        $t("Final Box Price")
                                        }}</span>
                                    </td>
                                    <td class="text-right">
                                        <span class="font-weight-bold">{{
                                        product.final_box_price.toFixed(2)
                                        }}</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </b-row>
            </b-card>
        </b-col>
          <!-- Warehouse Quantity -->
          <!-- <b-col md="8" class="mt-4" v-if="product.type == 'is_single'">
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
          </b-col> -->

          <!-- Warehouse Variants Quantity -->
          <!-- <b-col md="7" v-if="product.type == 'is_variant'" class="mt-4">
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
          </b-col> -->
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
        .get(`get_costing_detail/${id}`)
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
