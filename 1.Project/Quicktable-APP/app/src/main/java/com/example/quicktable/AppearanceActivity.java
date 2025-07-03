package com.example.quicktable;

import android.content.Intent;
import android.os.AsyncTask;
import android.os.Bundle;
import android.widget.TableLayout;
import android.widget.TableRow;
import android.widget.TextView;
import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;
import org.json.JSONArray;
import org.json.JSONObject;
import java.io.BufferedReader;
import java.io.InputStreamReader;
import java.net.HttpURLConnection;
import java.net.URL;
import java.net.URLEncoder;

public class AppearanceActivity extends AppCompatActivity {

    private TableLayout tableLayout;
    private String fullName;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);
        setContentView(R.layout.activity_appearance);

        tableLayout = findViewById(R.id.table_layout);
        TextView txtUserName = findViewById(R.id.username);
        TextView txtUserPosition = findViewById(R.id.userposition);

        Intent intent = getIntent();
        fullName = intent.getStringExtra("full_name");
        String details = intent.getStringExtra("details");

        if (fullName != null) txtUserName.setText("Name: " + fullName);
        if (details != null) txtUserPosition.setText("Position: " + details);

        new FetchDataTask().execute(fullName);

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });
    }

    private class FetchDataTask extends AsyncTask<String, Void, String> {

        @Override
        protected String doInBackground(String... params) {
            String username = params[0];
            try {
                String encodedUsername = URLEncoder.encode(username, "UTF-8");
                URL url = new URL("http://192.168.201.3/QuicktableAPI/fetch_Appearance.php?full_name=" + encodedUsername);
                HttpURLConnection conn = (HttpURLConnection) url.openConnection();
                conn.setRequestMethod("GET");

                BufferedReader reader = new BufferedReader(new InputStreamReader(conn.getInputStream()));
                StringBuilder response = new StringBuilder();
                String line;
                while ((line = reader.readLine()) != null) {
                    response.append(line);
                }
                reader.close();
                return response.toString();
            } catch (Exception e) {
                e.printStackTrace();
                return null;
            }
        }

        @Override
        protected void onPostExecute(String result) {
            try {
                tableLayout.removeViews(1, tableLayout.getChildCount() - 1);

                if (result != null) {
                    JSONObject json = new JSONObject(result);
                    if (json.getBoolean("success")) {
                        JSONArray data = json.getJSONArray("data");
                        for (int i = 0; i < data.length(); i++) {
                            JSONObject item = data.getJSONObject(i);
                            String date = item.getString("date");
                            String status = item.getString("status");

                            TableRow row = new TableRow(AppearanceActivity.this);

                            TextView dateView = new TextView(AppearanceActivity.this);
                            dateView.setText(date);
                            dateView.setPadding(8, 8, 8, 8);
                            dateView.setTextColor(getResources().getColor(R.color.black));
                            dateView.setTextSize(14);
                            dateView.setGravity(android.view.Gravity.CENTER);
                            row.addView(dateView);

                            TextView statusView = new TextView(AppearanceActivity.this);
                            statusView.setText(status);
                            statusView.setPadding(8, 8, 8, 8);
                            statusView.setTextColor(getResources().getColor(R.color.black));
                            statusView.setTextSize(14);
                            statusView.setGravity(android.view.Gravity.CENTER);
                            row.addView(statusView);

                            tableLayout.addView(row);
                        }
                    }
                }
            } catch (Exception e) {
                e.printStackTrace();
            }
        }
    }
}