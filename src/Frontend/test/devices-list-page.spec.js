import DevicesListPage from "../src/devices/list/devices-list-page.vue";
import {afterEachHooks, beforeEachHooks, mount} from 'vue-unit';

describe('a.vue', function () {
    beforeEach(beforeEachHooks);
    afterEach(afterEachHooks);

    // asserting JavaScript options
    it('should have correct message', function () {
        mount(DevicesListPage);
        expect($('.component')).to.contain('Hello')
    })

})
