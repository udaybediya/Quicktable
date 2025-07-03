package com.example.quicktable;

import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ListView;
import android.widget.SimpleAdapter;
import android.widget.Toast;

import androidx.appcompat.app.AppCompatActivity;

import org.json.JSONArray;
import org.json.JSONObject;

import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.util.ArrayList;
import java.util.Collections;
import java.util.Comparator;
import java.util.HashMap;
import java.util.List;

public class MenuActivity extends AppCompatActivity {

    private ListView listView;
    private EditText searchBox;
    private ArrayList<HashMap<String, String>> itemList;
    private ArrayList<HashMap<String, String>> filteredList;
    private Button backButton;
    private SimpleAdapter adapter;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_menu);

        listView = findViewById(R.id.listView);
        searchBox = findViewById(R.id.searchBox);
        backButton = findViewById(R.id.buttonBack);
        itemList = new ArrayList<>();
        filteredList = new ArrayList<>();

        // Initial data fetch
        fetchDataFromServer();

        // Set up back button click listener
        backButton.setOnClickListener(v -> finish());

        // Set up search functionality
        searchBox.addTextChangedListener(new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {
            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
                filterItems(s.toString());
            }

            @Override
            public void afterTextChanged(Editable s) {
            }
        });
    }

    private void fetchDataFromServer() {
        new Thread(() -> {
            try {
                URL url = new URL("http://192.168.201.3/QuicktableAPI/fetch_menu.php");
                HttpURLConnection urlConnection = (HttpURLConnection) url.openConnection();
                urlConnection.setRequestMethod("GET");

                InputStreamReader in = new InputStreamReader(urlConnection.getInputStream());
                int data = in.read();
                StringBuilder stringBuilder = new StringBuilder();
                while (data != -1) {
                    char current = (char) data;
                    stringBuilder.append(current);
                    data = in.read();
                }

                JSONArray itemsArray = new JSONArray(stringBuilder.toString());
                itemList.clear();
                for (int i = 0; i < itemsArray.length(); i++) {
                    JSONObject item = itemsArray.getJSONObject(i);
                    HashMap<String, String> map = new HashMap<>();
                    map.put("name", item.getString("item_name"));
                    map.put("price", "₹" + item.getString("item_price"));
                    map.put("type", item.getString("item_type"));
                    itemList.add(map);
                }

                Collections.sort(itemList, new Comparator<HashMap<String, String>>() {
                    @Override
                    public int compare(HashMap<String, String> o1, HashMap<String, String> o2) {
                        String type1 = o1.get("type");
                        String type2 = o2.get("type");
                        List<String> order = new ArrayList<>();
                        Collections.addAll(order, "SOUTH INDIAN", "PUNJABI", "GUJARATI", "ROTIES & TANDURI", "BEVERAGES", "SALAD’S & PAPAD");
                        int index1 = order.indexOf(type1);
                        int index2 = order.indexOf(type2);
                        return Integer.compare(index1, index2);
                    }
                });

                filteredList.addAll(itemList);

                runOnUiThread(() -> {
                    adapter = new SimpleAdapter(
                            MenuActivity.this,
                            filteredList,
                            R.layout.list_item,
                            new String[]{"name", "price"},
                            new int[]{R.id.itemName, R.id.itemPrice}
                    );
                    listView.setAdapter(adapter);
                });

            } catch (Exception e) {
                runOnUiThread(() -> Toast.makeText(MenuActivity.this, "Error: " + e.getMessage(), Toast.LENGTH_SHORT).show());
            }
        }).start();
    }

    private void filterItems(String query) {
        filteredList.clear();
        for (HashMap<String, String> item : itemList) {
            if (item.get("name").toLowerCase().contains(query.toLowerCase())) {
                filteredList.add(item);
            }
        }
        adapter.notifyDataSetChanged();
    }
}
