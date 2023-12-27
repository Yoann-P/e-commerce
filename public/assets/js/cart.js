window.onload = () => {
  const mainContent = document.querySelector(".main_content");
  const tbody = document.querySelector("tbody");
  const cart_total_amounts = document.querySelectorAll(".cart_total_amount");

  let cart = JSON.parse(mainContent?.dataset?.cart || false);

  // function pour le format des prix
  const formatPrice = (price) => {
    return Intl.NumberFormat("en-US", {
      style: "currency",
      currency: "EUR",
    }).format(price);
  };
  // function de message flash
  const addFlashMessage = (message, status = "success") => {
    const text = `
    <div class="alert alert-${status}" role="alert">
  ${message}
</div>
    `;
    const audio = document.createElement("audio");
    audio.src = "/assets/audios/success.wav";

    audio.play();
    // console.log(text);
    document.querySelector(".notification").innerHTML += text;

    setTimeout(() => {
      document.querySelector(".notification").innerHTML = "";
    }, 2000);
  };
  // function de récupération des données
  const fetchData = async (requestUrl) => {
    const response = await fetch(requestUrl);

    return await response.json();
  };
  // fonctions d'écoute sur l'event
  const manageLink = async (event) => {
    event.preventDefault();
    const link = event.target.href
      ? event.target.href
      : event.target.parentNode;
    const requestUrl = link.href;
    cart = await fetchData(requestUrl);

    const productId = requestUrl.split("/")[5];
    const product = await fetchData("/product/get/" + productId);

    // console.log({ product });

    if (requestUrl.search("/add/") != -1) {
      // add to cart
      if (product) {
        addFlashMessage(`Produit ${product.name} ajouté au panier`);
      } else {
        addFlashMessage("Produit ajouté au panier");
      }

      // console.log("Produit ajouté au panier");
    }
    if (requestUrl.search("/remove/") != -1) {
      //remove from cart
      if (product) {
        addFlashMessage(`Produit ${product.name} rétiré du panier`, "danger");
      } else {
        addFlashMessage("Produit retiré du panier", "danger");
      }

      // console.log("Produit retiré du panier");
    }
    initCart();
    updateheaderCart();

    // console.log(link);
    // console.log(requestUrl);
    // console.log(result);
  };
  const addEvenListenerToLink = () => {
    const links = document.querySelectorAll("tbody a");
    links.forEach((link) => {
      link.addEventListener("click", manageLink);
    });
    const add_to_cart_links = document.querySelectorAll(
      "li.add-to-cart a, a.item_remove"
    );
    // console.log(add_to_cart_links);
    add_to_cart_links.forEach((link) => {
      link.addEventListener("click", manageLink);
    });
  };
  // fonctions pour le panier
  const initCart = () => {
    if (!cart) {
      addEvenListenerToLink();
      return;
    }

    if (tbody) {
      tbody.innerHTML = "";
      cart.items.forEach((item) => {
        const { product, quantity, sub_total } = item;
        const content = `
      <tr>
                  <td class="product-thumbnail">
                    <a
                      ><img
                        width="50"
                        alt="product1"
                        src="/assets/images/products/${product.imageUrls[0]}"
                    /></a>

                  </td>
                  <td data-title="Product" class="product-name">
                    <a>${product.name}</a>
                  </td>
                  <td data-title="Price" class="product-price">
                    ${formatPrice(product.soldePrice / 100)}
                  </td>
                  <td data-title="Quantity" class="product-quantity">
                    <div class="quantity">
                      <a href="/cart/remove/${product.id}/1">
                        <input type="button" value="-" class="minus" />
                      </a>
                      <input
                        type="text"
                        name="quantity"
                        value="${quantity}"
                        title="Qty"
                        size="4"
                        class="qty"
                      />
                      <a href="/cart/add/${product.id}/1">
                        <input type="button" value="+" class="plus" />
                      </a>
                    </div>
                  </td>
                  <td data-title="Total" class="product-subtotal">
                    ${formatPrice(sub_total / 100)}
                  </td>
                  <td data-title="Remove" class="product-remove">
                    <a
                      href="/cart/remove/${product.id}/${quantity}"
                    >
                      <i class="ti-close"></i>
                    </a>
                  </td>
    </tr>
      `;

        tbody.innerHTML += content;
      });
      cart_total_amounts.forEach((cart_total_amount) => {
        cart_total_amount.innerHTML = formatPrice(cart.sub_total / 100);
      });
    }

    addEvenListenerToLink();
  };
  //function de gestion de la cart du header
  const updateheaderCart = async () => {
    const cart_count = document.querySelector(".cart_count");
    const cart_list = document.querySelector(".cart_list");
    const cart_price_value = document.querySelector(".cart_price_value");
    if (!cart) {
      //cart not found
      cart = await fetchData("/cart/get");
    }
    // cart data found
    cart_count.innerHTML = cart.cart_count;
    cart_price_value.innerHTML = formatPrice(cart.sub_total / 100);

    cart_list.innerHTML = "";
    cart.items.forEach((item) => {
      const { product, quantity, sub_total } = item;
      cart_list.innerHTML += `
            <li>
            <a href="/cart/remove/${
              product.id
            }/${quantity}" class="item_remove">
              <i class="ion-close"></i>
            </a>
            <a href="/product/${product.slug}"
              ><img
                width="50"
                height="50"
                alt="cart_thumb1"
                src="/assets/images/products/${product.imageUrls[0]}"
              />${product.name}</a
            >
            <span class="cart_quantity">
              ${quantity} x 
              <span class="cart_amount">
                <span class="price_symbole">${formatPrice(
                  product.soldePrice / 100
                )}</span>
              </span>
            </span>
          </li>
            `;
    });
    addEvenListenerToLink();
  };
  initCart();
  updateheaderCart();
  // console.log(cart);
};
